# STOXVISION AI - CONTENT REPORT
**VERSION 1.0 (2026)**
**PREPARED FOR: KRISTU JAYANTI COLLEGE (AUTONOMOUS)**

---

## 1.0 INTRODUCTION
StoXVision AI is a premium, high-fidelity stock market analysis platform designed to empower retail investors with institutional-grade technical insights and neural-network-backed predictions. Built using the LAMP stack (Linux, Apache, MySQL, PHP), StoXVision merges real-time financial data with advanced sentiment analysis to provide a holistic view of market dynamics.

Unlike traditional brokerage apps, StoXVision focuses on "Actionable Intelligence," transforming noisy market data into clean, visual trade plans. Administrators have granular control over API orchestration, ensuring high availability through intelligent key rotation.

### 1.1 PROBLEM DEFINITION:
The modern financial landscape is characterized by "Information Overload." Retail investors often jump between multiple platforms—one for news, another for charts, and a third for portfolio tracking. Manual calculation of technical indicators like RSI or EMA is prone to error and time-consuming. Furthermore, existing free tools often lack predictive capabilities or professional-grade UI, discouraging new users from consistent market engagement.

### 1.2 SCOPE OF THE PROJECT:
*   **Neural Analysis Engine**: Development of algorithms to process historical price data and generate short-term market projections.
*   **Market Orchestration**: Integration with Alpha Vantage and Finnhub APIs to provide real-time candlestick charts and news sentiment.
*   **User Centralization**: A unified dashboard for tracking a personalized watchlist (Portfolio) with real-time price updates.
*   **Administrative Hardening**: A robust back-office for managing API rate limits, user status, and system diagnostics.
*   **Premium Aesthetics**: An interface designed for maximum visual impact, utilizing glassmorphism and high-contrast dark modes.

---

## 1.3 MODULES IN THE PROJECT:
*   **User Identity & Auth**: Secure onboarding and session management using PHP.
*   **Real-time Analysis Engine**: The core "Analyze" module for fetching and processing ticker data.
*   **Neural Prediction Module**: AI-driven confidence scoring and direction projection.
*   **Sentiment Scraper**: Aggregating global financial news to gauge market mood.
*   **Portfolio Management**: A persistent database-backed watchlist for user-specific tickers.
*   **Admin Diagnostics**: System monitoring and API key health management.

---

## 2.0 SYSTEM STUDY
### 2.1 EXISTING SYSTEM STUDY
Currently, many retail users rely on:
*   **Manual Excel Sheets**: Tedious data entry and lack of real-time updates.
*   **Static News Portals**: Disconnected from actual price action.
*   **Generic Brokerage Apps**: Often lacking advanced indicators or predictive "Trade Plans."
*   **High-Cost Subscriptions**: Bloomberg or Reuters terminals are inaccessible to individual students or small traders.

### 2.2 FEASIBILITY STUDY:
*   **Technical Feasibility**: The project uses PHP 8.1+ and MySQL 8.0, utilizing the highly efficient `mysqli` driver. Compatibility with XAMPP ensures easy local deployment.
*   **Economic Feasibility**: By leveraging free-tier APIs and open-source libraries (Chart.js, Tailwind), the operational cost remains negligible while delivering high value.
*   **Behavioral Feasibility**: The system features an "Apple-like" premium UI, making it highly engaging for users who are otherwise intimidated by "green-and-red" financial charts.
*   **Schedule Feasibility**: The development is structured into a 44-day sprints covering Initiation, Logic Definition, UI Design, API Integration, and Final Hardening.

### 2.3 PROPOSED SYSTEM:
*   **Automated Insights**: One-click analysis of any NSE/BSE symbol.
*   **Scalable Architecture**: Database cached data to minimize API dependency.
*   **Security First**: CSRF protection, salted password hashing, and role-based access control.

---

## 3.0 SYSTEM DESIGN
### 3.1 ER DIAGRAM (STOXVISION)
The database structure consists of five primary entities:
1.  **Users**: Stores identity and role (Admin/User).
2.  **Stock Cache**: Temporarily stores processed market data to reduce API calls.
3.  **Watchlist**: Maps users to their tracked symbols.
4.  **API Keys**: Manages the rotation and health of external data streams.
5.  **Search History**: Tracks trending tickers across the platform.

### 3.2 TABLE DESIGN (SQL SCHEMA)

| Table | Column | Type | Constraint |
|:---|:---|:---|:---|
| **users** | id, name, email, password, role | INT, VARCHAR, ENUM | Primary Key, Unique Email |
| **stock_cache** | id, symbol, current_price, raw_data | INT, VARCHAR, DECIMAL | Unique Symbol |
| **watchlist** | id, user_id, symbol, added_at | INT, INT, VARCHAR | Foreign Key (users.id) |
| **api_keys** | id, api_key, status, usage_count | INT, VARCHAR, ENUM | Unique Key |

---

## 3.5 INPUT/OUTPUT DESIGN
### 3.5.1 CORE LOGIN LOGIC (login.php)
```php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
    }
}
```

### 3.5.2 ANALYSIS ENGINE CODE (analyze.php)
```php
$marketDataService = new MarketDataService($api_key);
$indicators = $marketDataService->calculateIndicators($raw_data);
$newsService = new NewsService($api_key);
$news_data = $newsService->getNewsSentiment($symbol);
$report = $reportService->generateReport($indicators, $symbol, $news_data);
```

---

## 4.0 SYSTEM CONFIGURATION
### 4.1 HARDWARE REQUIREMENTS
*   **Processor**: Intel Core i3 / AMD Ryzen 3 (or higher)
*   **RAM**: 4GB Minimum (8GB Recommended for browser multitasking)
*   **SSD**: 500MB free space for database growth
*   **Internet**: Stable connection for API synchronization

### 4.2 SOFTWARE REQUIREMENTS
*   **Front End**: HTML5, Vanilla CSS3 (Custom Design System), JavaScript (ES6+)
*   **Back End**: PHP 8.1 / 8.2
*   **Database**: MySQL 8.0 (MariaDB)
*   **Environment**: XAMPP / WAMP Server
*   **IDE**: Visual Studio Code

---

## 6.0 TESTING
**SDLC Incremental Model**: StoXVision was developed in modules, allowing for testing each feature (Auth, API, Charting) in isolation before integration.

### 6.1 TEST CASES
1.  **Symbol Validation**: Testing if the system rejects symbols containing special characters (e.g., `<script>`). Result: Handled by Regex.
2.  **API Rate Limiting**: Simulating API 429 errors. Result: System successfully rotates to the next available key in the `api_keys` table.
3.  **Broken Session**: Accessing `dashboard.php` without logging in. Result: Correctly redirected to `index.php` by `auth_check.php`.

---

## 7.0 VALIDATION
*   **7.1 Symbol Validation**: Uses `preg_match("/^[A-Z0-9\.\-]+$/", $symbol)` to ensure clean ticker requests.
*   **7.2 Security Validation**: CSRF tokens are injected into all POST requests (Watchlist/Login) to prevent cross-site request forgery.
*   **7.3 Data Integrity**: `DECIMAL(15, 2)` types ensure financial data precision, avoiding floating-point errors.

---

## 8.0 CONCLUSION & FUTURE ENHANCEMENT
### 8.1 CONCLUSION
StoXVision AI successfully bridges the gap between complex market analytics and retail accessibility. By automating indicator calculations and sentiment analysis, it reduces the cognitive load on the investor, enabling more disciplined trading decisions.

### 8.2 FUTURE ENHANCEMENT
*   **Webhooks**: Real-time price alerts via Telegram or Email.
*   **Machine Learning (RNN)**: Moving from static algorithms to Recurrent Neural Networks for trend prediction.
*   **Dark Pool Tracking**: Monitoring institutional block trades for advanced users.

---

## 9.0 BIBLIOGRAPHY
1.  "Modern PHP: New Features and Good Practices" by Josh Lockhart.
2.  "Technical Analysis of the Financial Markets" by John J. Murphy.
3.  W3Schools Financial Data Tutorials.
4.  Chart.js Documentation (https://www.chartjs.org/).
