# CognitPro AI Prompt Marketplace

<div align="center">

![CognitPro Logo](https://via.placeholder.com/200x80/3B82F6/FFFFFF?text=CognitPro)

**The Leading Global Marketplace for AI Prompts**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19-61DAFB?style=for-the-badge&logo=react)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0-3178C6?style=for-the-badge&logo=typescript)](https://www.typescriptlang.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.0-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)

[🚀 Live Demo](https://cognitpro.com) • [📖 Documentation](./docs/) • [🐛 Report Bug](https://github.com/cognitpro/issues) • [💡 Request Feature](https://github.com/cognitpro/issues)

</div>

## 🌟 Overview

CognitPro is a comprehensive Software-as-a-Service (SaaS) platform that connects prompt engineers with customers seeking high-quality AI prompts for various generative AI models. Our marketplace enables sellers to monetize their prompt engineering expertise while providing buyers with access to professionally crafted prompts that enhance their AI interactions.

### ✨ Key Features

- 🎯 **Specialized Marketplace** - Focused exclusively on AI prompts
- 💰 **Multiple Pricing Models** - Fixed price, pay-what-you-want, and free prompts
- 🔐 **Secure Transactions** - Integrated payment processing with Stripe, PayPal, and Paddle
- ⭐ **Quality Assurance** - Verified purchase system and comprehensive review mechanisms
- 🌍 **Global Reach** - Multi-currency support and international payouts
- 📊 **Advanced Analytics** - Detailed insights for sellers and platform administrators
- 🔍 **Smart Discovery** - Advanced search, filtering, and recommendation engine
- 👥 **Community-Driven** - Built-in social features and seller verification system

## 🏗️ Architecture

### Technology Stack

**Backend**
- **Framework**: Laravel 12.x (PHP 8.3+)
- **Database**: MySQL 8.0 with Redis caching
- **Authentication**: Laravel Fortify with Sanctum API tokens
- **Real-time**: Laravel Reverb WebSocket server
- **Queue Processing**: Redis with Laravel Horizon
- **Testing**: PHPUnit and Pest

**Frontend**
- **Framework**: React 19 with TypeScript
- **UI Library**: ShadCN UI components with Tailwind CSS
- **State Management**: Redux Toolkit
- **Routing**: Inertia.js for seamless SPA experience
- **Build Tool**: Vite with hot module replacement
- **Testing**: Jest, React Testing Library, Cypress

**Infrastructure**
- **Hosting**: Cloud hosting (AWS/DigitalOcean)
- **Web Server**: Nginx with PHP-FPM
- **File Storage**: Cloud object storage (S3/Spaces)
- **CDN**: Content delivery network for global performance
- **Monitoring**: Application performance monitoring

### Database Schema

Our robust database design includes 11+ tables with proper relationships:

- **Users** - Role-based user management (buyer/seller/admin)
- **Categories** - Hierarchical prompt categorization
- **Tags** - Flexible tagging system
- **Prompts** - Core prompt entities with version control
- **Purchases** - Transaction tracking and ownership
- **Reviews** - Rating and feedback system
- **Payments** - Payment processing and commission tracking
- **Payouts** - Seller earnings and payout management
- **User Profiles** - Extended user information and preferences

## 🚀 Quick Start

### Prerequisites

- PHP 8.3 or higher
- Node.js 22 or higher
- MySQL 8.0
- Redis server
- Composer
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/cognitpro/cognitpro.git
   cd cognitpro
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure your `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cognitpro
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   REDIS_HOST=127.0.0.1
   REDIS_PASSWORD=null
   REDIS_PORT=6379

   # Payment Gateway Configuration
   STRIPE_KEY=your_stripe_key
   STRIPE_SECRET=your_stripe_secret
   PAYPAL_CLIENT_ID=your_paypal_client_id
   PAYPAL_CLIENT_SECRET=your_paypal_client_secret
   ```

6. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Build frontend assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

### Development Workflow

**Start development servers:**
```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite development server (hot reload)
npm run dev

# Terminal 3: Queue worker (for background jobs)
php artisan queue:work

# Terminal 4: WebSocket server (for real-time features)
php artisan reverb:start
```

## 📊 Current Implementation Status

### ✅ Completed Features (Phase 1A & 1B - 100%)

**Backend Foundation**
- ✅ Complete database schema with 11 tables and proper relationships
- ✅ 9 Eloquent models with full business logic and relationships
- ✅ 5 comprehensive API controllers with CRUD operations
- ✅ 3 business logic services (Payment, Payout, Prompt)
- ✅ Role-based middleware system (buyer/seller/admin)
- ✅ Advanced authorization and security measures

**Frontend Foundation**
- ✅ 5 reusable React components with TypeScript
- ✅ 3 main pages (Homepage, Prompts List, Prompt Detail)
- ✅ Responsive design with ShadCN UI and Tailwind CSS
- ✅ Advanced search and filtering capabilities
- ✅ Modern UI with proper color scheme integration

### 🚧 In Progress (Phase 1C - 80%)

- ⏳ Seller Dashboard implementation
- ⏳ User Profile Management pages
- ⏳ Category browsing interface
- ⏳ Prompt creation/editing forms

### 📋 Next Steps (Phase 1D - Integration & Testing)

1. **API Route Registration** - Connect frontend to backend
2. **Web Controllers** - Create controllers for serving page data
3. **Payment Gateway Integration** - Implement Stripe/PayPal
4. **Testing Suite** - PHPUnit and Jest test implementation
5. **Production Deployment** - Staging and production setup

**Overall Progress: ~75% Complete for MVP**

## 🎯 User Roles & Features

### 👨‍💻 Prompt Engineers (Sellers)
- **Profile Management** - Public seller profiles with portfolio showcase
- **Prompt Creation** - Rich editor with preview and version control
- **Pricing Flexibility** - Fixed price, pay-what-you-want, or free prompts
- **Analytics Dashboard** - Sales metrics, earnings, and performance insights
- **Payout Management** - Automated payouts via Payoneer integration
- **Review System** - Customer feedback and rating management

### 🛒 Customers (Buyers)
- **Advanced Search** - Smart filtering by category, tags, price, and rating
- **Secure Purchases** - Multiple payment options with instant access
- **Review & Rating** - Share feedback and rate purchased prompts
- **Purchase History** - Track all bought prompts and downloads
- **Favorites** - Save prompts for later purchase
- **Recommendations** - AI-powered prompt suggestions

### 🔧 Administrators
- **User Management** - Account oversight and verification system
- **Content Moderation** - Prompt approval and quality control
- **Analytics Dashboard** - Platform metrics and performance monitoring
- **Commission Settings** - Configurable revenue sharing models
- **Payment Management** - Transaction oversight and dispute resolution
- **Platform Configuration** - System settings and feature toggles

## 🔐 Security Features

- **Role-Based Access Control** - Granular permissions for different user types
- **Secure Authentication** - Laravel Fortify with 2FA support
- **Payment Security** - PCI-compliant payment processing
- **Data Protection** - GDPR compliance and data encryption
- **API Security** - Rate limiting and request validation
- **Content Verification** - Automated and manual content moderation

## 🧪 Testing Strategy

### Backend Testing
- **Unit Tests** - Model and service layer testing with PHPUnit
- **Feature Tests** - API endpoint and integration testing
- **Database Testing** - Migration and seeding validation
- **Security Testing** - Authentication and authorization testing

### Frontend Testing
- **Component Tests** - React component testing with Jest
- **Integration Tests** - User flow testing with React Testing Library
- **E2E Testing** - Complete user journey testing with Cypress
- **Accessibility Testing** - WCAG compliance validation

### Quality Assurance
- **Code Coverage** - Minimum 80% test coverage requirement
- **Static Analysis** - PHPStan and ESLint for code quality
- **Performance Testing** - Load testing and optimization
- **Security Audits** - Regular security assessments

## 📚 API Documentation

Our RESTful API provides comprehensive endpoints for all platform functionality:

### Authentication Endpoints
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/login` - User authentication
- `POST /api/v1/auth/logout` - Session termination
- `POST /api/v1/auth/forgot-password` - Password reset

### Prompt Management
- `GET /api/v1/prompts` - List prompts with filtering
- `GET /api/v1/prompts/{id}` - Get prompt details
- `POST /api/v1/prompts` - Create new prompt (sellers only)
- `PUT /api/v1/prompts/{id}` - Update prompt (owner only)
- `DELETE /api/v1/prompts/{id}` - Delete prompt (owner only)

### Purchase & Reviews
- `POST /api/v1/purchases` - Purchase a prompt
- `GET /api/v1/purchases` - User purchase history
- `POST /api/v1/reviews` - Submit prompt review
- `GET /api/v1/reviews` - Get prompt reviews

For complete API documentation, see [API Specification](./docs/api-spec.md).

## 🎨 UI/UX Design

### Design System
- **Color Palette** - Professional blue and green theme with dark mode support
- **Typography** - Clean, readable fonts optimized for content consumption
- **Components** - Consistent ShadCN UI components with custom styling
- **Responsive Design** - Mobile-first approach with tablet and desktop optimization
- **Accessibility** - WCAG 2.1 AA compliance with keyboard navigation support

### User Experience
- **Intuitive Navigation** - Clear information architecture and user flows
- **Fast Performance** - Optimized loading times and smooth interactions
- **Search & Discovery** - Advanced filtering and recommendation systems
- **Trust Indicators** - Seller verification, reviews, and security badges
- **Seamless Checkout** - Streamlined purchase process with multiple payment options

## 🚀 Deployment

### Production Environment

**Server Requirements**
- Ubuntu 20.04+ or CentOS 8+
- Nginx 1.18+
- PHP 8.3+ with required extensions
- MySQL 8.0+
- Redis 6.0+
- SSL certificate

**Deployment Steps**
1. **Server Setup**
   ```bash
   # Install required packages
   sudo apt update
   sudo apt install nginx mysql-server redis-server php8.3-fpm

   # Configure PHP extensions
   sudo apt install php8.3-mysql php8.3-redis php8.3-gd php8.3-curl
   ```

2. **Application Deployment**
   ```bash
   # Clone and setup application
   git clone https://github.com/cognitpro/cognitpro.git
   cd cognitpro
   composer install --optimize-autoloader --no-dev
   npm ci && npm run build

   # Configure environment
   cp .env.production .env
   php artisan key:generate
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Web Server Configuration**
   ```nginx
   server {
       listen 443 ssl http2;
       server_name cognitpro.com;
       root /var/www/cognitpro/public;

       ssl_certificate /path/to/certificate.crt;
       ssl_certificate_key /path/to/private.key;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
       }
   }
   ```

### CI/CD Pipeline

We use GitHub Actions for automated testing and deployment:

```yaml
name: CI/CD Pipeline
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test

  deploy:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - name: Deploy to production
        run: # Deployment script
```

## 📈 Performance & Monitoring

### Performance Optimization
- **Database Optimization** - Proper indexing and query optimization
- **Caching Strategy** - Redis caching for frequently accessed data
- **CDN Integration** - Global content delivery for static assets
- **Image Optimization** - Automatic image compression and WebP conversion
- **Code Splitting** - Lazy loading for React components
- **Bundle Optimization** - Tree shaking and minification

### Monitoring & Analytics
- **Application Monitoring** - Real-time performance tracking
- **Error Tracking** - Automated error reporting and alerting
- **User Analytics** - Comprehensive user behavior tracking
- **Business Metrics** - Revenue, conversion, and engagement tracking
- **Infrastructure Monitoring** - Server health and resource utilization

## 🤝 Contributing

We welcome contributions from the community! Please read our [Contributing Guidelines](./CONTRIBUTING.md) before submitting pull requests.

### Development Guidelines
1. **Code Style** - Follow PSR-12 for PHP and Prettier for JavaScript/TypeScript
2. **Testing** - Write tests for all new features and bug fixes
3. **Documentation** - Update documentation for any API or feature changes
4. **Security** - Follow security best practices and report vulnerabilities responsibly

### Getting Started
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) file for details.

## 🆘 Support

- **Documentation** - [Complete documentation](./docs/)
- **Issues** - [GitHub Issues](https://github.com/cognitpro/issues)
- **Discussions** - [GitHub Discussions](https://github.com/cognitpro/discussions)
- **Email** - support@cognitpro.com

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [React](https://reactjs.org) - A JavaScript library for building user interfaces
- [ShadCN UI](https://ui.shadcn.com) - Beautifully designed components
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [Inertia.js](https://inertiajs.com) - The modern monolith

---

<div align="center">

**Built with ❤️ by the CognitPro Team**

[Website](https://cognitpro.com) • [Twitter](https://twitter.com/cognitpro) • [LinkedIn](https://linkedin.com/company/cognitpro)

</div>
