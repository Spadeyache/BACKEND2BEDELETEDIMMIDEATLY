# Deploying veara-backend to Google Cloud Run

This backend deploys to **Cloud Run** via a **Cloud Build trigger** that fires on every
push to the production branch of `github.com/jonathan-veara/veara-backend`. The build is
defined in `[cloudbuild.yaml](../cloudbuild.yaml)`: it builds the Docker image, pushes it
to Artifact Registry, and deploys it to Cloud Run.

- **Database:** hosted Supabase Postgres (public pooler connection — no VPC/Cloud SQL needed).
- **Secrets:** the entire production `.env` is stored as a single **Secret Manager** secret
(`veara-backend-env`) and mounted into the container as a file at `/var/www/html/.env`.
Nothing sensitive is set as a plain Cloud Run env var.

You run the **one-time setup** below once. After that, `git push` deploys.

---





## 0. Prerequisites (one time, on your machine)

```bash
# Install the Google Cloud SDK if you don't have it: https://cloud.google.com/sdk/docs/install
gcloud auth login
gcloud config set project project-4e3ae124-305e-478e-be4
gcloud config set run/region us-east1

# Handy variables used throughout this doc:
export PROJECT_ID=project-4e3ae124-305e-478e-be4
export REGION=us-east1
export SERVICE=veara-backend
export REPO=cloud-run-source-deploy
export ENV_SECRET=veara-backend-env
export PROJECT_NUMBER=$(gcloud projects describe $PROJECT_ID --format='value(projectNumber)')
```

## 1. Enable the required APIs

```bash
gcloud services enable \
  run.googleapis.com \
  cloudbuild.googleapis.com \
  artifactregistry.googleapis.com \
  secretmanager.googleapis.com
```

## 2. Create the Artifact Registry repo

`cloudbuild.yaml` pushes to `us-east1-docker.pkg.dev/project-4e3ae124-305e-478e-be4/cloud-run-source-deploy/...`,
so the repo must exist with that exact name:

```bash
gcloud artifacts repositories create $REPO \
  --repository-format=docker \
  --location=$REGION \
  --description="Cloud Run images for veara-backend"
```

## 3. Build the production `.env` and store it as a secret

Create a local file `prod.env` with the real production values. Start from
`[docs/supabase-hosted-setup.md](supabase-hosted-setup.md)` and `.env.example`. It must
include **all** of the following (grouped):

```env
# --- App ---
APP_NAME=Veara
APP_ENV=production
APP_DEBUG=false
APP_KEY=                      # generate: php artisan key:generate --show   (or: echo "base64:$(openssl rand -base64 32)")
APP_URL=https://PLACEHOLDER   # set to the Cloud Run URL after first deploy (see step 7), or your custom domain
LOG_CHANNEL=stderr            # so logs land in Cloud Logging, not a file
LOG_LEVEL=error

# --- Backend database (hosted Supabase, session pooler) ---
DB_CONNECTION=pgsql
DB_HOST=aws-0-<region>.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-ref>
DB_PASSWORD=<supabase-db-password>
DB_SCHEMA=public
DB_SSLMODE=require

# --- Design-source database (same or separate Supabase project) ---
VEARA_DESIGN_DB_HOST=...
VEARA_DESIGN_DB_PORT=5432
VEARA_DESIGN_DB_DATABASE=postgres
VEARA_DESIGN_DB_USERNAME=postgres.<project-ref>
VEARA_DESIGN_DB_PASSWORD=<supabase-db-password>
VEARA_DESIGN_DB_SCHEMA=public
VEARA_DESIGN_DB_SSLMODE=require

# --- Drivers (Cloud Run is stateless: keep state in Postgres) ---
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# --- File storage (Supabase Storage, S3-compatible) ---
FILESYSTEM_DISK=supabase
SUPABASE_PROJECT_REF=<project-ref>
SUPABASE_STORAGE_BUCKET=design-catalog
SUPABASE_STORAGE_REGION=auto
SUPABASE_STORAGE_ENDPOINT=https://<project-ref>.storage.supabase.co/storage/v1/s3
SUPABASE_STORAGE_ACCESS_KEY_ID=<storage-s3-access-key>
SUPABASE_STORAGE_SECRET_ACCESS_KEY=<storage-s3-secret-key>
SUPABASE_STORAGE_PUBLIC_URL=https://<project-ref>.supabase.co/storage/v1/object/public/design-catalog
SUPABASE_STORAGE_USE_PATH_STYLE_ENDPOINT=true

# --- Auth / JWT ---
JWT_SECRET=<generate: php artisan jwt:secret --show>

# --- Admin bootstrap (render-start.sh syncs this user on boot) ---
VEARA_ADMIN_EMAIL=admin@veara.com
VEARA_ADMIN_PASSWORD=<long-random-password>
VEARA_ADMIN_FIRST_NAME=admin
VEARA_ADMIN_LAST_NAME=admin

# --- Integrations ---
STRIPE_KEY=...
STRIPE_SECRET=...
STRIPE_WEBHOOK_SECRET=...
PRINTIFY_BEARER_TOKEN=...
PRINTIFY_SHOP_ID=...
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=https://PLACEHOLDER/api/v1/social-login   # update after first deploy
APPLE_CLIENT_ID=...
APPLE_CLIENT_SECRET=...
APPLE_REDIRECT_URI=https://PLACEHOLDER/...                    # update after first deploy

# --- Mail ---
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@veara.com
MAIL_FROM_NAME=Veara

# --- Frontend ---
FRONTEND_URL=https://your-website-domain
```

> Generate `APP_KEY` and `JWT_SECRET` locally (commands shown inline above) — they must be
> stable across deploys or existing sessions/tokens break.

Create the secret from that file (then delete the local copy):

```bash
gcloud secrets create $ENV_SECRET --replication-policy=automatic
gcloud secrets versions add $ENV_SECRET --data-file=prod.env
rm prod.env
```

To change any value later: edit `prod.env`, run `gcloud secrets versions add $ENV_SECRET --data-file=prod.env`, then redeploy (push, or `gcloud run services update $SERVICE --region=$REGION` to pick up `:latest`).

## 4. Grant IAM

**Cloud Run runtime service account** (reads the secret at runtime). By default Cloud Run
uses the Compute Engine default service account:

```bash
gcloud secrets add-iam-policy-binding $ENV_SECRET \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

**Cloud Build service account** (builds the image and runs `gcloud run deploy`). The trigger
uses the default Cloud Build SA `${PROJECT_NUMBER}@cloudbuild.gserviceaccount.com`; grant it
deploy + impersonation rights:

```bash
gcloud projects add-iam-policy-binding $PROJECT_ID \
  --member="serviceAccount:${PROJECT_NUMBER}@cloudbuild.gserviceaccount.com" \
  --role="roles/run.admin"

gcloud projects add-iam-policy-binding $PROJECT_ID \
  --member="serviceAccount:${PROJECT_NUMBER}@cloudbuild.gserviceaccount.com" \
  --role="roles/iam.serviceAccountUser"
```

(Artifact Registry write + log write are granted to the Cloud Build SA by default. If a
build fails on push/log perms, also add `roles/artifactregistry.writer` and
`roles/logging.logWriter`.)

## 5. Connect GitHub and create the trigger

Connect the repo once (opens a browser for GitHub authorization):

```bash
# Easiest via console: Cloud Build > Triggers > Connect Repository (2nd-gen, GitHub).
# Or CLI, after the GitHub connection exists:
gcloud builds triggers create github \
  --name="veara-backend-deploy" \
  --repo-owner="jonathan-veara" \
  --repo-name="veara-backend" \
  --branch-pattern="^main$" \
  --build-config="cloudbuild.yaml" \
  --region="$REGION"
```

> Adjust `--branch-pattern` if production deploys from a branch other than `main`.

## 6. Push the changes and trigger the first build

Commit the updated `cloudbuild.yaml` (and this doc) and push to the production branch:

```bash
git add cloudbuild.yaml docs/DEPLOY_GCP.md
git commit -m "Configure Cloud Run deploy: secret-mounted .env + deploy flags"
git push origin main
```

Watch the build:

```bash
gcloud builds list --region=$REGION --ongoing
gcloud beta run services logs tail $SERVICE --region=$REGION   # boot + migration logs
```

## 7. Post-deploy: set the real URLs

Get the service URL:

```bash
gcloud run services describe $SERVICE --region=$REGION --format='value(status.url)'
```

Then update `APP_URL`, `GOOGLE_REDIRECT_URI`, `APPLE_REDIRECT_URI`, and `FRONTEND_URL` (if
needed) in `prod.env`, add a new secret version (step 3), and redeploy. Also point your
**Stripe** and **Printify** webhook endpoints at:

- `https://<service-url>/api/v1/webhooks/stripe`
- `https://<service-url>/api/v1/webhooks/printify`

## 8. Verify

```bash
curl -fsS https://<service-url>/up && echo "  <- health OK"
```

`/up` is Laravel's health route. A 200 means nginx + php-fpm + DB config loaded. The admin
UI lives at `/admin/dashboard` (login via the `VEARA_ADMIN_*` user synced on boot).

---

## Notes & gotchas

- **Migrations run on every cold start** (`docker/render-start.sh` → `php artisan migrate --force`). This is fine but means the deploying revision must be able to reach Supabase.
- `**.env` is read-only at runtime.** The admin Settings screens that rewrite SMTP/Stripe/
Printify keys into `.env` (`SettingController`) will fail on Cloud Run — manage those
values through the secret instead. (They never persisted on the old Render host either.)
- **No VPC connector needed** as long as Supabase is reached over its public pooler
hostname. If you later lock Supabase to private networking, add a Serverless VPC connector.
- **Rollback:** `gcloud run services update-traffic $SERVICE --region=$REGION --to-revisions=<previous-revision>=100`.

