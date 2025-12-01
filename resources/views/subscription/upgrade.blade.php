@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">

            {{-- ALREADY PREMIUM --}}
            @if(Auth::user()->school->isPremium())
                <div class="text-center py-5">
                    <i class="fas fa-crown text-warning" style="font-size: 5rem;"></i>
                    <h1 class="display-3 fw-bold text-success mt-4">You Are PREMIUM!</h1>
                    <p class="lead">Your subscription expires on:</p>
                    <h2 class="text-primary fw-bold">
                        {{ Auth::user()->school->subscription_expires_at->format('d F Y') }}
                    </h2>
                    <a href="{{ route('dashboard') }}" class="btn btn-lg btn-outline-success mt-4">
                        Go to Dashboard
                    </a>
                </div>
            @else

                {{-- HERO SECTION --}}
                <div class="text-center mb-5">
                    <h1 class="display-3 fw-bold text-primary">
                        Upgrade to <span class="text-warning">Premium</span> Today
                    </h1>
                    <p class="lead text-muted fs-4">Join over 50+ schools already using E-School Zambia Premium</p>
                </div>

                {{-- CURRENT STATUS ALERT --}}
                <div class="alert alert-danger text-center rounded-4 shadow-lg border-0 mb-5">
                    <i class="fas fa-exclamation-circle fa-2x"></i><br>
                    <strong class="fs-3">You are currently on the FREE PLAN</strong><br>
                    @if(Auth::user()->school->subscription_expires_at)
                        <span class="text-dark">Expired on {{ Auth::user()->school->subscription_expires_at->format('d F Y') }}</span>
                    @else
                        <span class="text-dark">Never activated</span>
                    @endif
                </div>

                {{-- PRICING CARDS — YEARLY IS KING --}}
                <div class="row g-4 justify-content-center mb-5">
                    {{-- Monthly Plan --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <h4 class="fw-bold text-muted">Monthly</h4>
                                <div class="my-4">
                                    <span class="display-4 fw-bold text-primary">K450</span>
                                    <small class="text-muted">/ month</small>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li><i class="fas fa-check text-success"></i> All Premium Features</li>
                                    <li><i class="fas fa-check text-success"></i> WhatsApp Results</li>
                                    <li><i class="fas fa-check text-success"></i> Delete Incomes</li>
                                </ul>
                                <a href="https://wa.me/260765574796?text=Hello!%20I%20want%20to%20pay%20K450%20Monthly%20for%20{{ urlencode(Auth::user()->school->name) }}%20Premium"
                                   target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary w-100">Choose Monthly</a>
                            </div>
                        </div>
                    </div>

                    {{-- Term Plan --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-4">
                                <h4 class="fw-bold text-muted">Per Term</h4>
                                <div class="my-4">
                                    <span class="display-4 fw-bold text-info">K1,700</span>
                                    <small class="text-muted">/ term</small>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li><i class="fas fa-check text-success"></i> All Premium Features</li>
                                    <li><i class="fas fa-check text-success"></i> 4 Months Access</li>
                                    <li><i class="fas fa-check text-success"></i> Best for short-term</li>
                                </ul>
                                <a href="https://wa.me/260765574796?text=Hello!%20I%20want%20to%20pay%20K1,700%20Per%20Term%20for%20{{ urlencode(Auth::user()->school->name) }}%20Premium"
                                  target="_blank" rel="noopener noreferrer" class="btn btn-outline-info w-100">Choose Term</a>
                            </div>
                        </div>
                    </div>

                    {{-- YEARLY PLAN — MOST POPULAR & VISUALLY DOMINANT --}}
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-lg position-relative overflow-hidden"
                             style="border: 3px solid #ffc107 !important; transform: scale(1.05); z-index: 10;">
                            <div class="position-absolute top-0 start-50 translate-middle-x bg-warning text-dark px-4 py-2 rounded-bottom shadow">
                                <strong>MOST POPULAR</strong>
                            </div>
                            <div class="card-body text-center p-5 bg-gradient" style="background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);">
                                <h3 class="fw-bold text-success">Annual Plan</h3>
                                <div class="my-4">
                                    <span class="display-3 fw-bold text-success">K5,000</span>
                                    <small class="text-muted d-block">per year</small>
                                    <div class="badge bg-success text-white fs-6 mt-2">
                                        Save K3,800 vs Monthly!
                                    </div>
                                    <div class="text-success fw-bold mt-2">
                                        Only <u>K416 per month</u>
                                    </div>
                                </div>
                                <ul class="list-unstyled text-start mb-4">
                                    <li><i class="fas fa-check text-success fa-lg"></i> <strong>Full Year Access</strong></li>
                                    <li><i class="fas fa-check text-success fa-lg"></i> <strong>Best Value</strong></li>
                                    <li><i class="fas fa-check text-success fa-lg"></i> All Premium Features</li>
                                    <li><i class="fas fa-check text-success fa-lg"></i> Priority Support</li>
                                </ul>
                                <a href="https://wa.me/260765574796?text=Hello!%20I%20want%20to%20pay%20K5,000%20Yearly%20(MOST%20POPULAR)%20for%20{{ urlencode(Auth::user()->school->name) }}%20Premium"
                                  target="_blank" rel="noopener noreferrer" class="btn btn-success btn-lg w-100 shadow-lg">
                                    <i class="fab fa-whatsapp"></i> Choose Annual (Recommended)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONTACT SECTION --}}
                <div class="text-center py-5 bg-light rounded-4 shadow">
                    <h2 class="fw-bold text-dark mb-4">How to Pay & Activate Instantly</h2>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="bg-white p-5 rounded-4 shadow-sm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="text-start">
                                            <i class="fas fa-envelope text-primary fa-3x mb-3"></i>
                                            <h5>Email Us</h5>
                                            <a href="mailto:contact@e-schoolzambia.site" class="fs-5">contact@e-schoolzambia.site</a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-start">
                                            <i class="fab fa-whatsapp text-success fa-3x mb-3"></i>
                                            <h5>WhatsApp / Call</h5>
                                            <a href="https://wa.me/260765574796" class="fs-5 text-success">+260 765 574 796</a>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <p class="text-muted">
                                    <strong>Payment Methods:</strong> Mobile Money (MTN, Airtel, Zamtel) • Bank Transfer • Cash Deposit<br>
                                    <strong>We activate your Premium within 10 minutes of payment!</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>



                {{-- Explaining premium features --}}
                <div class="container my-5">
                    <div class="p-4 p-md-5 bg-white shadow rounded-4 border border-2">

                        <h2 class="text-center fw-bold mb-4">
                            🌟 E-School Zambia <span class="text-primary">Premium</span> – The Complete Upgrade
                        </h2>

                        <p class="text-muted text-center fs-5 mb-5">
                            Premium transforms your school into a modern, efficient, data-driven institution.
                            Every feature is designed to save time, reduce workload, eliminate errors, and improve
                            communication with parents and teachers.
                        </p>

                        <!-- Feature List -->
                        <div class="list-group">

                            <!-- 1. Ranking -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-success">✅ 1. Automated Class Positioning (Ranking System)</h4>
                                <p class="mb-2">The system automatically calculates each learner’s class position based on all subjects.</p>
                                <ul class="mb-3">
                                    <li>No spreadsheets or manual ranking</li>
                                    <li>Updates instantly when marks change</li>
                                    <li>Supports ties, weighting rules, and custom grading logic</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Provides accurate, transparent rankings for reports, awards, and performance monitoring.</p>
                            </div>

                            <!-- 2. Raw marks -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-success">✅ 2. Raw Marks + Automatic Percentages</h4>
                                <p class="mb-2">Teachers enter marks and the system computes everything automatically:</p>
                                <ul class="mb-3">
                                    <li>Percentages</li>
                                    <li>Grades</li>
                                    <li>Weighted averages</li>
                                    <li>Total term performance</li>
                                </ul>
                                <p><strong>Benefit:</strong> Eliminates errors and improves academic transparency.</p>
                            </div>

                            <!-- 3. Custom incomes -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-success">✅ 3. Custom Incomes (Create, Edit, Delete)</h4>
                                <p>Track every revenue stream beyond school fees:</p>
                                <ul class="mb-3">
                                    <li>PTA funds, donations, grants</li>
                                    <li>Fundraisers & miscellaneous income</li>
                                    <li>Full records: date, term, year, source, description</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Gives a complete financial picture for budgeting and audits.</p>
                            </div>

                            <!-- 4. Reports -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-success">✅ 4. Expense Reports & Income Reports</h4>
                                <p>Generate detailed reports by term or year.</p>
                                <ul class="mb-3">
                                    <li>Filter by term, year, category</li>
                                    <li>Download PDF or Excel</li>
                                    <li>Compare performance across terms</li>
                                    <li>Drill-down income & expense tracking</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Essential for board meetings, audits, donor reporting, and accountability.</p>
                            </div>

                            <!-- 5. Income statement -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-success">✅ 5. Financial Reporting & Income Statement (Profit/Loss)</h4>
                                <p>Premium automatically generates a true accounting-grade income statement:</p>
                                <ul class="mb-3">
                                    <li>Total revenues & expenses</li>
                                    <li>Net surplus/deficit</li>
                                    <li>Top income & cost categories</li>
                                    <li>Historical comparisons</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Enables proper planning, budgeting, and decision-making.</p>
                            </div>

                            <!-- 6. WhatsApp/SMS -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-primary">🌟 6. Sending Results to Parents via WhatsApp & SMS</h4>
                                <p>This upcoming feature will transform communication:</p>
                                <ul class="mb-3">
                                    <li>Send individual student results via WhatsApp</li>
                                    <li>SMS notifications for newly posted results</li>
                                    <li>Bulk sending for entire classes or school</li>
                                    <li>Personalized messages with name, class, position, etc.</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Guarantees parents receive results immediately — no printing delays or lost reports.</p>
                            </div>

                            <!-- 7. Inventory -->
                            <div class="list-group-item py-4">
                                <h4 class="fw-bold text-primary">🌟 7. School Inventory Management</h4>
                                <p>This upcoming feature allows your school to manage supplies and track usage:</p>
                                <ul class="mb-3">
                                    <li>Chalk, pens, exercise books, textbooks</li>
                                    <li>Cleaning materials & lab supplies</li>
                                    <li>Add categories, track in/out movements</li>
                                    <li>Low stock alerts & inventory reports</li>
                                </ul>
                                <p><strong>Why it matters:</strong> Prevents wastage, theft, shortages, and improves school resource planning.</p>
                            </div>
                        </div>

                        <!-- Additional Benefits -->
                        <div class="mt-5">
                            <h3 class="fw-bold text-center mb-3">🛠 Additional Premium Benefits</h3>

                            <ul class="list-group shadow-sm">
                                <li class="list-group-item py-3">
                                    <strong>✔ 24/7 Technical Support</strong><br>
                                    Full support for registration, data entry, result imports, troubleshooting, and onboarding.
                                </li>
                                <li class="list-group-item py-3">
                                    <strong>✔ Guaranteed Data Security</strong><br>
                                    Automatic backups, activity logs, and secure storage.
                                </li>
                                <li class="list-group-item py-3">
                                    <strong>✔ Faster Workflows for Staff</strong><br>
                                    Admin tasks that took hours now take minutes.
                                </li>
                                <li class="list-group-item py-3">
                                    <strong>✔ Improved Parent Satisfaction</strong><br>
                                    Instant communication and professional reporting.
                                </li>
                            </ul>
                        </div>

                        <!-- Summary -->
                        <div class="alert alert-success mt-5 rounded-4 p-4 text-center">
                            <h4 class="fw-bold mb-3">🌟 Why Schools Choose Premium</h4>
                            <p class="mb-0 fs-5">
                                Premium gives your school the same tools used by the top institutions in Zambia —
                                <strong>at a fraction of the cost.</strong><br>
                                Efficient academics, accurate reporting, parent engagement, financial intelligence,
                                24/7 support, and zero technical stress.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Trust Footer --}}
                <div class="text-center mt-5">
                    <p class="text-muted">
                        Trusted by leading schools in Lusaka • Ndola • Kitwe • Livingstone • Chipata • Solwezi
                    </p>
                </div>

            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card:hover { transform: translateY(-5px); transition: all 0.3s; }
    .badge { font-size: 0.9rem !important; }
</style>
@endsection
