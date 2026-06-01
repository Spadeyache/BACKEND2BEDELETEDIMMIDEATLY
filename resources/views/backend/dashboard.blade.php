@extends('backend.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid veara-dashboard" id="kt_content">
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <div class="veara-kicker">Operations Console</div>
                    <h1 class="veara-page-title my-1 fs-2">Dashboard</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Overview</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <div class="container-xxl">
                <section class="veara-hero">
                    <div>
                        <div class="veara-kicker">Admin Activity</div>
                        <h1>Keep the Veara backend moving with a calmer command center.</h1>
                        <p>Track the core queues, revenue checkpoints, and operational tasks from one report-inspired surface.</p>
                    </div>
                    <span class="veara-status-badge">Live Workspace</span>
                </section>

                <section class="veara-metric-grid mb-6">
                    <article class="veara-metric">
                        <div class="veara-metric-label">Current Orders</div>
                        <div class="veara-metric-value">237</div>
                        <div class="veara-metric-note">30 active, 45 completed, 25 waiting.</div>
                    </article>
                    <article class="veara-metric veara-metric-accent">
                        <div class="veara-metric-label">Project Finance</div>
                        <div class="veara-metric-value">$3,290</div>
                        <div class="veara-metric-note">Average project budget is trending up.</div>
                    </article>
                    <article class="veara-metric">
                        <div class="veara-metric-label">Support Inbox</div>
                        <div class="veara-metric-value">18</div>
                        <div class="veara-metric-note">New contact requests to triage.</div>
                    </article>
                    <article class="veara-metric">
                        <div class="veara-metric-label">System Readiness</div>
                        <div class="veara-metric-value">92%</div>
                        <div class="veara-metric-note">Stripe and Printify settings are configured.</div>
                    </article>
                </section>

                <div class="row g-6 g-xl-9">
                    <div class="col-xl-5">
                        <section class="veara-panel">
                            <div class="veara-panel-head">
                                <div class="veara-panel-label">Workstream</div>
                                <span class="veara-panel-tag">Progress</span>
                            </div>
                            <div class="veara-panel-body">
                                <div class="d-flex flex-wrap align-items-center gap-5 mb-5">
                                    <div class="d-flex flex-center h-100px w-100px">
                                        <canvas id="kt_project_list_chart" width="100" height="100"></canvas>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="veara-metric-label">Order Flow</div>
                                        <div class="veara-metric-value fs-1">50%</div>
                                        <div class="veara-progress mt-3" aria-label="Order flow 50% complete">
                                            <span style="width: 50%"></span>
                                        </div>
                                    </div>
                                </div>

                                <ul class="veara-list">
                                    <li>
                                        <span class="veara-list-mark">A</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">Active orders</div>
                                            <div class="text-muted">30 currently moving through fulfillment.</div>
                                        </div>
                                        <div class="fw-bold text-dark">30</div>
                                    </li>
                                    <li>
                                        <span class="veara-list-mark">C</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">Completed</div>
                                            <div class="text-muted">45 items closed out cleanly.</div>
                                        </div>
                                        <div class="fw-bold text-dark">45</div>
                                    </li>
                                    <li>
                                        <span class="veara-list-mark">Q</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-dark">Queued</div>
                                            <div class="text-muted">25 need the next operational action.</div>
                                        </div>
                                        <div class="fw-bold text-dark">25</div>
                                    </li>
                                </ul>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-4">
                        <section class="veara-panel">
                            <div class="veara-panel-head">
                                <div class="veara-panel-label">Finance Notes</div>
                                <span class="veara-panel-tag">Snapshot</span>
                            </div>
                            <div class="veara-panel-body">
                                <div class="veara-timeline">
                                    <div class="veara-timeline-item">
                                        <div class="veara-timeline-title">Average project budget</div>
                                        <div class="veara-timeline-copy">$6,570 is the current benchmark for planning checks.</div>
                                    </div>
                                    <div class="veara-timeline-item">
                                        <div class="veara-timeline-title">Lowest project check</div>
                                        <div class="veara-timeline-copy">$408 is the low-water mark to monitor for margin drift.</div>
                                    </div>
                                    <div class="veara-timeline-item">
                                        <div class="veara-timeline-title">Ambassador page</div>
                                        <div class="veara-timeline-copy">$920 is tracking upward and ready for review.</div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-3">
                        <section class="veara-panel">
                            <div class="veara-panel-head">
                                <div class="veara-panel-label">Priority</div>
                                <span class="veara-panel-tag">Today</span>
                            </div>
                            <div class="veara-panel-body">
                                <div class="veara-kicker">In Progress</div>
                                <h2 class="fs-3 fw-bold text-dark mb-2">Fitness App</h2>
                                <p class="mb-5 text-muted">CRM application work to improve HR efficiency.</p>

                                <div class="border border-dashed rounded p-4 mb-4">
                                    <div class="fw-bold text-dark">Feb 21, 2024</div>
                                    <div class="text-muted">Due date</div>
                                </div>
                                <div class="border border-dashed rounded p-4 mb-5">
                                    <div class="fw-bold text-dark">$284,900.00</div>
                                    <div class="text-muted">Budget</div>
                                </div>

                                <div class="veara-progress" aria-label="Priority project 50% complete">
                                    <span style="width: 50%"></span>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
    @endpush
@endsection
