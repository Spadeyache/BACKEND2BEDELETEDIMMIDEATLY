<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    // =============================== account setting =============================
    public function index()
    {
        $setting = Setting::first();

        if ($setting) {
            $setting->logo = $setting->logo
                ? helpers::generateTempURL($setting->logo, config('app.file_system'))
                : null;

            $setting->favicon = $setting->favicon
                ? helpers::generateTempURL($setting->favicon, config('app.file_system'))
                : null;
        }

        return view('backend.layout.Setting.index',compact('setting'));
    }

    public function store(Request $request)
    {

        $validData = $request->validate([
            'title'     => 'nullable|string|max:255',
            'copyright' => 'nullable|string|max:255',
            'logo'      => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'favicon'   => 'nullable|file|mimes:jpg,jpeg,png,ico|max:1024',
        ]);


        try {
            $setting = Setting::first() ?? new Setting();

            foreach (['logo', 'favicon'] as $type) {
                if ($request->hasFile($type)) {
                    if (!empty($setting->$type)) {
                        Helpers::deleteFile($setting->$type, config('app.file_system'));
                    }

                    $file = $request->file($type);
                    $filename = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                    $setting->$type = Helpers::uploadFile($type, $file, $filename, config('app.file_system'));
                }
            }

            $setting->fill([
                'system_title'     => $validData['title'],
                'copyright_text'   => $validData['copyright'],
            ])->save();

            return redirect()->back()->with('success', 'Settings saved successfully.');

        }catch (\Exception $exception){
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

    // ======================================= smtp setting ==========================================
    public function smtpIndex()
    {
        return view('backend.layout.Setting.smtp_index');
    }
    public function smtpStore(Request $request)
    {
        $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'required|string|in:tls,ssl',
            'mail_from_address' => 'required|email',
        ]);

        try {
            $envContent = File::get(base_path('.env'));
            $lineBreak = "\n";
            $envContent = preg_replace([
                '/MAIL_MAILER=(.*)\s/',
                '/MAIL_HOST=(.*)\s/',
                '/MAIL_PORT=(.*)\s/',
                '/MAIL_USERNAME=(.*)\s/',
                '/MAIL_PASSWORD=(.*)\s/',
                '/MAIL_ENCRYPTION=(.*)\s/',
                '/MAIL_FROM_ADDRESS=(.*)\s/',
            ], [
                'MAIL_MAILER=' . $request->mail_mailer . $lineBreak,
                'MAIL_HOST=' . $request->mail_host . $lineBreak,
                'MAIL_PORT=' . $request->mail_port . $lineBreak,
                'MAIL_USERNAME=' . $request->mail_username . $lineBreak,
                'MAIL_PASSWORD=' . $request->mail_password . $lineBreak,
                'MAIL_ENCRYPTION=' . $request->mail_encryption . $lineBreak,
                'MAIL_FROM_ADDRESS=' . $request->mail_from_address . $lineBreak,
            ], $envContent);

            if ($envContent !== null) {
                File::put(base_path('.env'), $envContent);
            }
            return redirect()->back()->with('success', 'SMTP credentials updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $content = preg_replace("/^{$key}=(.*)/", "{$key}={$value}", $content);
        }

        file_put_contents($envPath, $content);
    }

    public function stripeKeysIndex()
    {
        return view('backend.layout.Setting.stripe_index');
    }
    
    public function stripeKeysStore(Request $request)
    {
        $request->validate([
            'stripe_key' => 'required|string',
            'stripe_secret' => 'required|string',
            'stripe_webhook_secret' => 'required|string'
        ]);

        $keys_to_set = [
            'STRIPE_KEY'            => $request->stripe_key,
            'STRIPE_SECRET'         => $request->stripe_secret,
            'STRIPE_WEBHOOK_SECRET' => $request->stripe_webhook_secret
        ];

        try {
            $envContent = File::get(base_path('.env'));

            foreach ($keys_to_set as $key => $value) {
                if (preg_match("/^{$key}=/m", $envContent)) {
                    // Key exists — replace it
                    $envContent = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}={$value}",
                        $envContent
                    );

                    if ($envContent === null) {
                        return redirect()->back()->with('error', "Failed to update {$key} in .env");
                    }
                } else {
                    // Key missing — append it
                    $envContent .= "\n{$key}={$value}";
                }
            }

            File::put(base_path('.env'), $envContent);
            return redirect()->back()->with('success', 'Stripe credentials updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function printifyKeysIndex()
    {
        return view('backend.layout.Setting.printify_index');
    }
    
    public function printifyKeysStore(Request $request)
    {
        $request->validate([
            'printify_bearer_token' => 'required|string',
            // 'printify_shop_id'      => 'required|string'
        ]);

        $keys_to_set = [
            'PRINTIFY_BEARER_TOKEN' => $request->printify_bearer_token,
            // 'PRINTIFY_SHOP_ID'      => $request->printify_shop_id
        ];

        try {
            $envContent = File::get(base_path('.env'));

            foreach ($keys_to_set as $key => $value) {
                if (preg_match("/^{$key}=/m", $envContent)) {
                    // Key exists — replace it
                    $envContent = preg_replace(
                        "/^{$key}=.*/m",
                        "{$key}={$value}",
                        $envContent
                    );

                    if ($envContent === null) {
                        return redirect()->back()->with('error', "Failed to update {$key} in .env");
                    }
                } else {
                    // Key missing — append it
                    $envContent .= "\n{$key}={$value}";
                }
            }

            File::put(base_path('.env'), $envContent);
            return redirect()->back()->with('success', 'Printify credentials updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
