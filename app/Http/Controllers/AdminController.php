<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Country;
use App\Models\Port;
use App\Models\NewsArticle;
use App\Models\ExchangeRate;
use App\Models\ActivityLog;
use App\Models\RiskScore;
use App\Models\WeatherSnapshot;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ═══════════════════════════════════════
    // AUTH
    // ═══════════════════════════════════════

    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            Auth::logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'Silakan login menggunakan akun Administrator.']);
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // ✅ WAJIB: regenerate session SEGERA setelah attempt() berhasil
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->role !== 'admin') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('admin.login')
                    ->withErrors(['email' => 'Akun ini bukan Administrator'])
                    ->onlyInput('email');
            }

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => 'ADMIN_LOGIN',
                'description' => "Administrator {$user->email} logged into the admin panel.",
            ]);

            return redirect()->route('admin.dashboard')
                ->with('toast_success', 'Selamat datang, ' . $user->name . '!');
        }

        return back()->withErrors(['email' => 'Kredensial tidak valid.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $name = Auth::user()->name ?? 'Admin';
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('toast_info', $name . ' telah logout.');
    }

    // ═══════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════

    public function dashboard()
    {
        // Auto-seed if database is empty on launch
        if (Country::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        }

        $stats = [
            'total_users'        => User::count(),
            'active_users'       => User::where('role', 'user')->count(),
            'total_countries'    => Country::count(),
            'total_ports'        => Port::count(),
            'total_watchlists'   => Watchlist::count(),
            'total_news'         => NewsArticle::count(),
            'published_articles' => NewsArticle::whereNotNull('published_at')->count(),
            'draft_articles'     => NewsArticle::whereNull('published_at')->count(),
            'archived_articles'  => 0,
        ];

        // User registration last 7 days
        $userTrend = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Risk distribution
        $riskDist = RiskScore::selectRaw('risk_level, COUNT(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level');

        // Recent logs
        $logs = ActivityLog::with('user')->latest()->take(10)->get();

        // Latest users
        $latestUsers = User::latest()->take(5)->get();

        // API statuses
        $apis = [
            ['name' => 'News API',     'status' => 'Online', 'latency' => '120ms', 'uptime' => 99.8],
            ['name' => 'Weather API',  'status' => 'Online', 'latency' => '180ms', 'uptime' => 99.5],
            ['name' => 'Exchange API', 'status' => 'Online', 'latency' => '95ms',  'uptime' => 99.9],
            ['name' => 'Map Tiles',    'status' => 'Online', 'latency' => '45ms',  'uptime' => 100.0],
        ];

        // System summary details
        $systemSummary = [
            'high_risk'   => RiskScore::whereIn('risk_level', ['High', 'Critical'])->count(),
            'medium_risk' => RiskScore::where('risk_level', 'Medium')->count(),
            'low_risk'    => RiskScore::where('risk_level', 'Low')->count(),
            'api_status'  => 'Online',
            'db_status'   => 'Connected',
            'env'         => app()->environment(),
        ];

        // Additional chart: News by Category
        $newsByCategory = NewsArticle::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        // Additional chart: Country Distribution (Ports count per country)
        $countryDist = Port::selectRaw('countries.name as country_name, COUNT(ports.id) as count')
            ->join('countries', 'ports.country_id', '=', 'countries.id')
            ->groupBy('countries.name')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        try {
            DB::connection()->getPdo();
            $systemSummary['db_status'] = 'Connected';
        } catch (\Exception $e) {
            $systemSummary['db_status'] = 'Disconnected';
        }

        return view('admin.dashboard.index', compact(
            'stats', 'userTrend', 'riskDist', 'logs', 'latestUsers', 'apis', 'systemSummary', 'newsByCategory', 'countryDist'
        ));
    }

    // ═══════════════════════════════════════
    // USERS CRUD
    // ═══════════════════════════════════════

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8'],
            'role'     => ['required', 'in:admin,user'],
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'USER_CREATE',
            'description' => "Created new user: {$user->email} (role: {$user->role})",
        ]);

        return back()->with('toast_success', 'User berhasil dibuat.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'role'  => ['required', 'in:admin,user'],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['min:8']]);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'USER_UPDATE',
            'description' => "Updated user: {$user->email}",
        ]);

        return back()->with('toast_success', 'User berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('toast_error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }

        $email = $user->email;
        $user->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'USER_DELETE',
            'description' => "Deleted user: {$email}",
        ]);

        return back()->with('toast_success', 'User berhasil dihapus.');
    }

    // ═══════════════════════════════════════
    // COUNTRIES CRUD
    // ═══════════════════════════════════════

    public function countries()
    {
        $countries = Country::with('economic')->orderBy('name')->get();
        return view('admin.countries', compact('countries'));
    }

    public function updateCountry(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'code'   => ['required', 'string', 'max:5'],
            'region' => ['required', 'string'],
        ]);

        $country->update($request->only('name', 'code', 'region'));

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'COUNTRY_UPDATE',
            'description' => "Updated country: {$country->name}",
        ]);

        return back()->with('toast_success', 'Country berhasil diperbarui.');
    }

    public function destroyCountry($id)
    {
        $country = Country::findOrFail($id);
        $name = $country->name;
        $country->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'COUNTRY_DELETE',
            'description' => "Deleted country: {$name}",
        ]);

        return back()->with('toast_success', 'Country berhasil dihapus.');
    }

    // ═══════════════════════════════════════
    // PORTS CRUD
    // ═══════════════════════════════════════

    public function ports()
    {
        $ports = Port::with('country')->orderBy('name')->get();
        return view('admin.ports', compact('ports'));
    }

    public function updatePort(Request $request, $id)
    {
        $port = Port::findOrFail($id);

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        $port->update($request->only('name', 'latitude', 'longitude'));

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'PORT_UPDATE',
            'description' => "Updated port: {$port->name}",
        ]);

        return back()->with('toast_success', 'Port berhasil diperbarui.');
    }

    public function destroyPort($id)
    {
        $port = Port::findOrFail($id);
        $name = $port->name;
        $port->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'PORT_DELETE',
            'description' => "Deleted port: {$name}",
        ]);

        return back()->with('toast_success', 'Port berhasil dihapus.');
    }

    // ═══════════════════════════════════════
    // NEWS
    // ═══════════════════════════════════════

    public function news()
    {
        $news = NewsArticle::orderBy('fetched_at', 'desc')->take(200)->get();
        return view('admin.news', compact('news'));
    }

    public function destroyNews($id)
    {
        $article = NewsArticle::findOrFail($id);
        $title = $article->title;
        $article->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'NEWS_DELETE',
            'description' => "Deleted news article: " . substr($title, 0, 60),
        ]);

        return back()->with('toast_success', 'Artikel berhasil dihapus.');
    }

    // ═══════════════════════════════════════
    // EXCHANGE RATES
    // ═══════════════════════════════════════

    public function rates()
    {
        $rates = ExchangeRate::orderBy('target_currency')->get();
        return view('admin.rates', compact('rates'));
    }

    // ═══════════════════════════════════════
    // WEATHER
    // ═══════════════════════════════════════

    public function weather()
    {
        $weathers = WeatherSnapshot::with('country')->orderBy('fetched_at', 'desc')->take(100)->get();
        return view('admin.weather', compact('weathers'));
    }

    // ═══════════════════════════════════════
    // RISK ANALYTICS
    // ═══════════════════════════════════════

    public function risks()
    {
        $risks = RiskScore::with('country')->orderBy('total_score', 'desc')->get();
        $riskDist = RiskScore::selectRaw('risk_level, COUNT(*) as count')
            ->groupBy('risk_level')
            ->get();
        return view('admin.risks', compact('risks', 'riskDist'));
    }

    // ═══════════════════════════════════════
    // REPORTS
    // ═══════════════════════════════════════

    public function reports()
    {
        return view('admin.reports');
    }

    // ═══════════════════════════════════════
    // ACTIVITY LOGS
    // ═══════════════════════════════════════

    public function logs()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(50);
        return view('admin.logs', compact('logs'));
    }

    // ═══════════════════════════════════════
    // SETTINGS
    // ═══════════════════════════════════════

    public function settings()
    {
        $settings = session('admin_settings', [
            'system_name'      => 'Supply Chain Risk Intelligence',
            'system_version'   => '2.0',
            'smtp_host'        => env('MAIL_HOST', 'smtp.mailtrap.io'),
            'smtp_port'        => env('MAIL_PORT', 2525),
            'news_api_key'     => '••••••••••••••••',
            'weather_api_key'  => '••••••••••••••••',
            'exchange_api_key' => '••••••••••••••••',
            'map_provider'     => 'cartodb-voyager',
        ]);
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'system_name'      => ['required', 'string'],
            'system_version'   => ['nullable', 'string'],
            'smtp_host'        => ['nullable', 'string'],
            'smtp_port'        => ['nullable', 'numeric'],
            'news_api_key'     => ['nullable', 'string'],
            'weather_api_key'  => ['nullable', 'string'],
            'exchange_api_key' => ['nullable', 'string'],
            'map_provider'     => ['nullable', 'string'],
        ]);

        session(['admin_settings' => $data]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'SETTINGS_UPDATE',
            'description' => 'Updated system configuration settings.',
        ]);

        return back()->with('toast_success', 'Pengaturan berhasil disimpan.');
    }
}
