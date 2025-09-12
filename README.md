<div align="center">
  <h1>💰 Expense Tracker</h1>
  <p><strong>A modern web application for expense tracking with Laravel & Vue 3</strong></p>
  
  <img src="https://img.shields.io/badge/Status-100%25%20Complete-success?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Status">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Chart.js-FF6384?style=for-the-badge&logo=chart.js&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/i18n-Indonesian%20%7C%20English-blue?style=for-the-badge&logo=googletranslate&logoColor=white" alt="i18n">
</div>

---

## 📋 Table of Contents

- [✨ Features](#-features)
- [🛠️ Tech Stack](#️-tech-stack)
- [📸 Screenshots](#-screenshots)
- [🚀 Installation](#-installation)
- [⚙️ Configuration](#️-configuration)
- [🎮 Usage](#-usage)
- [📊 API Documentation](#-api-documentation)
- [🎨 UI Components](#-ui-components)
- [🌙 Dark Mode](#-dark-mode)
- [📱 Responsive Design](#-responsive-design)
- [🔄 Development Status](#-development-status)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

---

## ✨ Features

### 🔹 **Core Features**

- ✅ **Transaction Management** - Add, edit, delete income and expense transactions
- ✅ **Category Management** - Create and manage custom income/expense categories
- ✅ **Multi-Currency Support** - Support for IDR (Rupiah) and USD currencies
- ✅ **Dashboard Overview** - Real-time balance, monthly income/expense summaries
- ✅ **Data Visualization** - Interactive charts showing financial trends
- ✅ **Data Export** - Export transactions to CSV format

### 🔹 **UI/UX Features**

- ✅ **Dark/Light Mode** - Complete theme switching with persistent settings
- ✅ **Responsive Design** - Works seamlessly on desktop, tablet, and mobile
- ✅ **Modern Interface** - Clean, intuitive design with Tailwind CSS
- ✅ **Real-time Updates** - Dynamic data loading without page refreshes

### 🔹 **Advanced Features**

- ✅ **Advanced Filtering** - Filter transactions by date, category, type
- ✅ **Search Functionality** - Search transactions by description or category
- ✅ **Internationalization (i18n)** - Dynamic language switching (Indonesian/English)
- ✅ **Currency-Based Localization** - Auto language switch based on currency selection
- ✅ **Data Persistence** - All data stored securely in SQLite database
- ✅ **Settings Management** - Configurable currency and theme preferences

---

## 🛠️ Tech Stack

### **Backend**

- **Framework**: Laravel 11.x
- **Database**: SQLite (development), MySQL/PostgreSQL (production ready)
- **API**: RESTful API with JSON responses
- **Authentication**: Laravel Sanctum (ready for implementation)

### **Frontend**

- **Framework**: Vue.js 3.x (Composition API)
- **Styling**: Tailwind CSS 4.x
- **Charts**: Chart.js with vue-chartjs
- **Internationalization**: Custom i18n implementation with reactive language switching
- **Build Tool**: Vite
- **Icons**: Heroicons & Emoji icons

### **Development Tools**

- **Package Manager**: NPM
- **Development Server**: Laravel Herd / Artisan serve
- **Hot Reload**: Vite HMR
- **Code Quality**: ESLint, Prettier (configurable)

---

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- SQLite (default) or MySQL/PostgreSQL

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/expense-tracker.git
cd expense-tracker
```

### Step 2: Install Backend Dependencies

```bash
composer install
```

### Step 3: Install Frontend Dependencies

```bash
npm install
```

### Step 4: Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Database Setup

```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### Step 6: Build Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### Step 7: Start Development Server

```bash
# Start Laravel server
php artisan serve

# In another terminal, start Vite (for development)
npm run dev
```

Visit `http://localhost:8000` to see your application!

### 🌍 **Live Demo**

🔗 **Demo URL**: [http://expense-tracker.test](http://expense-tracker.test)

> 📝 **Note**: This is a local development URL. Replace with your actual demo/production URL when deployed

**Alternative Access Methods**:
- Local: `http://localhost:8000`
- Herd: `http://expense-tracker.test` (if using Laravel Herd)
- Production: `https://your-domain.com`

---

## ⚙️ Configuration

### Database Configuration

```env
# SQLite (Default)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# MySQL Alternative
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_tracker
DB_USERNAME=root
DB_PASSWORD=
```

### Application Settings

```env
APP_NAME="Expense Tracker"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000
```

---

## 🎮 Usage

### 🜊 Dashboard

- View your current balance, monthly income, and expenses
- Interactive charts showing financial trends over time
- Quick overview of recent transactions
- Currency switcher (IDR/USD)
- **Auto Language Switch**: Interface language automatically changes based on selected currency (IDR → Indonesian, USD → English)

### 💳 Transactions

- **Add New**: Click "+ Tambah Transaksi" to add income or expense
- **Edit**: Click "Edit" button on any transaction
- **Delete**: Click "Delete" button with confirmation
- **Filter**: Use advanced filters by date, category, or type
- **Search**: Search transactions by description or category name
- **Export**: Download transactions as CSV file

### 📂 Categories

- **Income Categories**: Manage sources of income (Salary, Business, etc.)
- **Expense Categories**: Manage expense types (Food, Transport, etc.)
- **Add Category**: Create custom categories with colors
- **Delete Category**: Remove categories (with transaction cleanup warning)

### 🌙 Theme Settings

- **Dark Mode Toggle**: Click moon/sun icon in header
- **Persistent Settings**: Theme preference saved locally
- **System Detection**: Auto-detect system theme preference on first visit

---

## 📊 API Documentation

### Base URL

```
GET|POST|PUT|DELETE /api/{endpoint}
```

### Endpoints

#### 🏠 Dashboard

```http
GET /api/dashboard?currency={IDR|USD}
```

#### 💰 Transactions

```http
GET    /api/transactions?currency={IDR|USD}&type={income|expense}&category_id={id}&start_date={date}&end_date={date}
POST   /api/transactions
PUT    /api/transactions/{id}
DELETE /api/transactions/{id}
```

#### 📁 Categories

```http
GET    /api/categories
POST   /api/categories
DELETE /api/categories/{id}
```

#### ⚙️ Settings

```http
GET /api/settings
PUT /api/settings
```

### Sample Responses

```json
{
    "success": true,
    "data": {
        "balance": 1500000,
        "monthly_income": 5000000,
        "monthly_expense": 3500000,
        "recent_transactions": [
            ...
        ]
    }
}
```

---

## 🎨 UI Components

### Vue 3 Components Structure

```
src/components/
├── Dashboard.vue      # Main dashboard with charts
├── Transaction.vue    # Transaction management
├── Categories.vue     # Category management
└── ChartComponent.vue # Reusable chart component
```

### Tailwind CSS Classes

- **Dark Mode**: All components support `dark:` variants
- **Responsive**: Mobile-first responsive design
- **Color Scheme**: Consistent gray/blue color palette
- **Typography**: Clean, readable font hierarchy

---

## 🌙 Dark Mode

### Implementation Details

- **Toggle**: HTML class-based theme switching
- **Persistence**: LocalStorage saves user preference
- **Components**: All UI components support dark variants
- **Charts**: Chart.js adapts to theme colors automatically
- **System Detection**: Respects `prefers-color-scheme` on first visit

### Technical Implementation

```javascript
// Auto theme detection
const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches

// Toggle function
const toggleDarkMode = () => {
    document.documentElement.classList.toggle('dark')
    localStorage.setItem('darkMode', isDarkMode.toString())
}
```

---

## 🌍 Internationalization (i18n)

### Language Support

- **Indonesian (ID)**: Default language when IDR currency is selected
- **English (EN)**: Default language when USD currency is selected
- **Auto-switching**: Language automatically changes based on currency selection
- **Comprehensive Coverage**: All UI text, labels, and messages are translated

### Technical Implementation

```javascript
// Language files structure
resources/lang/
├── id.json    # Indonesian translations
└── en.json    # English translations

// Auto language switching based on currency
const changeLanguage = (currency) => {
    const newLang = currency === 'IDR' ? 'id' : 'en'
    i18n.language.value = newLang
}
```

### Translation Coverage

- ✅ Navigation and headers
- ✅ Form labels and placeholders
- ✅ Button texts and actions
- ✅ Status messages and alerts
- ✅ Chart labels and legends
- ✅ Date and currency formatting

---

## 📱 Responsive Design

### Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 768px
- **Desktop**: > 768px

### Responsive Features

- ✅ Mobile-optimized navigation
- ✅ Responsive charts and tables
- ✅ Touch-friendly buttons and inputs
- ✅ Flexible grid layouts
- ✅ Optimized spacing and typography

---

## 🔄 Development Status

### ✅ **Completed Features**

- [x] **Phase 1**: Basic CRUD operations
- [x] **Phase 2**: Advanced filtering and search
- [x] **Phase 3**: Data visualization with charts
- [x] **Phase 4**: Complete dark mode implementation
- [x] **Phase 5**: Responsive UI improvements
- [x] **Phase 6**: CSV export functionality
- [x] **Phase 7**: Multi-currency support
- [x] **Phase 8**: Internationalization (i18n) implementation

### 🔧 **Current Status**

- ✅ Core functionality: **100% Complete**
- ✅ UI/UX: **100% Complete**
- ✅ Dark Mode: **100% Complete**
- ✅ Responsive Design: **100% Complete**
- ✅ Data Export: **100% Complete**
- ✅ Internationalization: **100% Complete**
- ✅ **Overall Project Status: 100% COMPLETE 🎉**

### 📈 **Project Stats**

- **Lines of Code**: ~3,000+ lines
- **Vue Components**: 4 main components (Dashboard, Transactions, Categories, Charts)
- **API Endpoints**: 10 RESTful endpoints
- **Database Tables**: 4 tables (users, categories, transactions, settings)
- **Supported Languages**: 2 languages (Indonesian, English)
- **Translation Keys**: 100+ translation strings
- **Supported Currencies**: 2 currencies (IDR, USD)
- **Development Time**: ~30+ hours
- **Features Implemented**: 15+ major features

---

## 🤝 Contributing

### Development Workflow

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

### Code Style

- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: Use ES6+ features and Vue 3 Composition API
- **CSS**: Use Tailwind utilities, avoid custom CSS when possible

### Issues

Found a bug or have a suggestion? Please [open an issue](../../issues) with:

- Clear description
- Steps to reproduce (for bugs)
- Expected vs actual behavior
- Screenshots (if applicable)

---

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

<div align="center">
  <p><strong>🎉 PROJECT 100% COMPLETE! 🎉</strong></p>
  <p><strong>Made with ❤️ using Laravel & Vue.js</strong></p>
  <p>Features include: CRUD Operations, Charts, Dark Mode, i18n, CSV Export, Multi-Currency Support</p>
  <p>If you found this project helpful, please give it a ⭐!</p>
  <p><em>Terima kasih! Thank you!</em> 🙏</p>
</div>
