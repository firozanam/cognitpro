# CognitPro AI Prompt Marketplace - Test User Credentials

## Overview

This document contains the login credentials for test users created during development. These users are automatically seeded when running the `TestDataSeeder` and are available for testing all marketplace functionality.

**⚠️ IMPORTANT SECURITY NOTE**: These are test credentials only. Never use these credentials in production environments. All test users use the same password for development convenience.

## Default Password

**All test users use the same password**: `password`

## Test User Accounts

### 1. Admin User
- **Email**: `admin@cognitpro.com`
- **Password**: `password`
- **Name**: Admin User
- **Role**: `admin`
- **Permissions**: Full platform access, user management, content moderation
- **Use Case**: Testing admin panel functionality, user management, platform settings

### 2. Seller Accounts

#### Seller 1 - John Seller
- **Email**: `seller1@cognitpro.com`
- **Password**: `password`
- **Name**: John Seller
- **Role**: `seller`
- **Profile**: 
  - Bio: "Experienced AI prompt engineer specializing in creative writing and business automation."
  - Website: https://johnseller.com
  - Location: San Francisco, CA
- **Sample Prompts**: 
  - Professional Email Writer Pro ($9.99)
  - Code Review Assistant ($12.99)
- **Use Case**: Testing prompt creation, seller dashboard, earnings management

#### Seller 2 - Jane Creator
- **Email**: `seller2@cognitpro.com`
- **Password**: `password`
- **Name**: Jane Creator
- **Role**: `seller`
- **Profile**:
  - Bio: "Creative AI enthusiast focused on marketing and content creation prompts."
  - Website: https://janecreator.com
  - Location: New York, NY
- **Sample Prompts**:
  - SEO Content Strategy Generator ($15.99)
  - Interactive Learning Module Creator (Free)
- **Use Case**: Testing different pricing models, seller profiles, content management

### 3. Buyer Accounts

#### Buyer 1 - Mike Buyer
- **Email**: `buyer1@cognitpro.com`
- **Password**: `password`
- **Name**: Mike Buyer
- **Role**: `buyer`
- **Purchase History**: Has purchased and reviewed multiple prompts
- **Use Case**: Testing purchase flow, payment processing, review system

#### Buyer 2 - Sarah Customer
- **Email**: `buyer2@cognitpro.com`
- **Password**: `password`
- **Name**: Sarah Customer
- **Role**: `buyer`
- **Purchase History**: Has purchased and reviewed multiple prompts
- **Use Case**: Testing user experience, search functionality, customer journey

## Sample Data Created

### Categories (6 categories)
1. **Writing & Content** (✍️) - Blue theme
2. **Business & Marketing** (💼) - Green theme
3. **Code & Development** (💻) - Purple theme
4. **Education & Learning** (📚) - Orange theme
5. **Creative & Design** (🎨) - Red theme
6. **Data & Analysis** (📊) - Cyan theme

### Tags (28 tags)
ChatGPT, GPT-4, Claude, Copywriting, SEO, Marketing, Social Media, Email, Blog, Creative Writing, Technical Writing, Business Plan, Strategy, Analysis, Research, Programming, Python, JavaScript, Web Development, Data Science, Machine Learning, Education, Tutorial, Learning, Design, Creative, Art, Brainstorming, Productivity

### Sample Prompts (4 prompts)
1. **Professional Email Writer Pro** - $9.99 (Fixed price, Featured)
2. **SEO Content Strategy Generator** - $15.99 (Fixed price, Featured)
3. **Code Review Assistant** - $12.99 (Fixed price)
4. **Interactive Learning Module Creator** - Free

### Sample Purchases & Reviews
- Each buyer has purchased and reviewed multiple prompts
- Reviews include ratings (3-5 stars) and detailed feedback
- All reviews are marked as verified purchases

## Testing Scenarios

### Authentication Testing
- Login/logout with different user roles
- Password reset functionality
- Email verification process
- Role-based access control

### Seller Functionality Testing
- Prompt creation and editing
- Pricing model selection (fixed, pay-what-you-want, free)
- Category and tag assignment
- Draft/published workflow
- Seller dashboard and analytics

### Buyer Functionality Testing
- Browse and search prompts
- Filter by category, tags, price range
- View prompt details and reviews
- Purchase flow with different pricing models
- Leave reviews and ratings

### Admin Functionality Testing
- User management and role assignment
- Content moderation and approval
- Platform settings configuration
- Analytics and reporting

### Payment Testing
- Purchase prompts with different pricing models
- Payment processing with Stripe (mock implementation)
- Commission calculation and seller payouts
- Refund processing and transaction history

## Database Seeding

To create these test users and sample data, run:

```bash
php artisan db:seed --class=TestDataSeeder
```

Or to refresh the entire database with test data:

```bash
php artisan migrate:fresh --seed
```

## Security Considerations

### Development Environment
- These credentials are safe to use in development and testing environments
- All users have verified email addresses for testing purposes
- Passwords are properly hashed using Laravel's Hash facade

### Production Environment
- **NEVER** use these credentials in production
- **NEVER** run TestDataSeeder in production
- Create proper admin accounts with strong, unique passwords
- Implement proper user registration and verification flows

## Additional Notes

### Email Verification
- All test users are created with `email_verified_at` set to current timestamp
- This bypasses email verification for testing convenience
- In production, implement proper email verification workflow

### User Profiles
- Only seller accounts have detailed profiles created
- Buyer accounts can be used to test profile creation functionality
- Admin account can be used to test user profile management

### Sample Content
- All prompts contain realistic, professional content
- Categories and tags represent real marketplace scenarios
- Reviews and ratings provide realistic user feedback data

### Testing Workflows
1. **Complete User Journey**: Register → Browse → Purchase → Review
2. **Seller Workflow**: Create Account → Add Prompts → Manage Sales → View Analytics
3. **Admin Workflow**: Moderate Content → Manage Users → Configure Platform

---

**Last Updated**: September 2025  
**Environment**: Development/Testing Only  
**Security Level**: Test Credentials - Not for Production Use
