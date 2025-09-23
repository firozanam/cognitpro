# CognitPro AI Prompt Marketplace - Database Design Document

## 1. Introduction

This document provides a comprehensive overview of the database design for the AI Prompt Marketplace. It details the entity relationship diagram, table structures, relationships, and indexing strategies to ensure optimal performance and data integrity.

## 2. Entity Relationship Diagram

[Visual ERD would be included here in an actual implementation]

## 3. Core Entities and Tables

### 3.1 Users Table

#### Description
Stores all user information including buyers, sellers, and administrators.

#### Schema
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255) NULL,
    two_factor_recovery_codes TEXT NULL,
    remember_token VARCHAR(100) NULL,
    profile_photo_path VARCHAR(2048) NULL,
    current_team_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role (role),
    INDEX idx_email (email),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- One-to-Many with prompts (user_id)
- One-to-Many with purchases (user_id)
- One-to-Many with reviews (user_id)
- One-to-Many with payments (user_id)
- One-to-Many with payouts (user_id)

### 3.2 Prompts Table

#### Description
Stores all prompt information including content, pricing, and metadata.

#### Schema
```sql
CREATE TABLE prompts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT NULL,
    category_id BIGINT UNSIGNED NULL,
    price_type ENUM('fixed', 'pay_what_you_want', 'free') DEFAULT 'fixed',
    price DECIMAL(10, 2) NULL,
    minimum_price DECIMAL(10, 2) NULL DEFAULT 5.00,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    version INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_category_status (category_id, status),
    INDEX idx_price_type (price_type),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_published_at (published_at),
    FULLTEXT idx_search (title, description, excerpt),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with users (user_id)
- Many-to-One with categories (category_id)
- Many-to-Many with tags (prompt_tags pivot table)
- One-to-Many with purchases (prompt_id)
- One-to-Many with reviews (prompt_id)

### 3.3 Categories Table

#### Description
Stores hierarchical category information for organizing prompts.

#### Schema
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    icon VARCHAR(255) NULL,
    color VARCHAR(7) NULL DEFAULT '#000000',
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active),
    INDEX idx_sort_order (sort_order),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Self-referencing for hierarchical structure (parent_id)
- One-to-Many with prompts (category_id)

### 3.4 Tags Table

#### Description
Stores tags for additional prompt categorization and discovery.

#### Schema
```sql
CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-Many with prompts (prompt_tags pivot table)

### 3.5 Prompt Tags Pivot Table

#### Description
Junction table for the many-to-many relationship between prompts and tags.

#### Schema
```sql
CREATE TABLE prompt_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prompt_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_prompt_tag (prompt_id, tag_id),
    INDEX idx_prompt (prompt_id),
    INDEX idx_tag (tag_id)
);
```

### 3.6 Purchases Table

#### Description
Records all prompt purchases including transaction details.

#### Schema
```sql
CREATE TABLE purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    prompt_id BIGINT UNSIGNED NOT NULL,
    price_paid DECIMAL(10, 2) NOT NULL,
    payment_gateway VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    metadata JSON NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_prompt_purchase (user_id, prompt_id),
    INDEX idx_user (user_id),
    INDEX idx_prompt (prompt_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_gateway (payment_gateway),
    INDEX idx_purchased_at (purchased_at),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with users (user_id)
- Many-to-One with prompts (prompt_id)

### 3.7 Reviews Table

#### Description
Stores user reviews and ratings for prompts.

#### Schema
```sql
CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    prompt_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255) NULL,
    review_text TEXT NULL,
    verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT TRUE,
    helpful_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_user_prompt_review (user_id, prompt_id),
    INDEX idx_prompt_rating (prompt_id, rating),
    INDEX idx_user_prompt (user_id, prompt_id),
    INDEX idx_approved (is_approved),
    INDEX idx_created_at (created_at),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with users (user_id)
- Many-to-One with prompts (prompt_id)

### 3.8 Payments Table

#### Description
Records all payment transactions including platform fees.

#### Schema
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    payment_gateway VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    metadata JSON NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status),
    INDEX idx_gateway (payment_gateway),
    INDEX idx_processed_at (processed_at),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with users (user_id)

### 3.9 Payouts Table

#### Description
Records seller payout transactions.

#### Schema
```sql
CREATE TABLE payouts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'USD',
    status ENUM('pending', 'processed', 'failed') DEFAULT 'pending',
    payoneer_transaction_id VARCHAR(255) NULL,
    metadata JSON NULL,
    scheduled_for DATE NOT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status),
    INDEX idx_scheduled_for (scheduled_for),
    INDEX idx_processed_at (processed_at),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with users (user_id)

### 3.10 User Profiles Table

#### Description
Stores additional user profile information.

#### Schema
```sql
CREATE TABLE user_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    bio TEXT NULL,
    website VARCHAR(255) NULL,
    location VARCHAR(255) NULL,
    social_links JSON NULL,
    payout_method ENUM('payoneer', 'paypal', 'bank_transfer') DEFAULT 'payoneer',
    payout_details JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_profile (user_id)
);
```

#### Relationships
- One-to-One with users (user_id)

### 3.11 Prompt Versions Table

#### Description
Stores version history for prompts, allowing users to track changes and rollback to previous versions.

#### Schema
```sql
CREATE TABLE prompt_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    prompt_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    excerpt TEXT NULL,
    category_id BIGINT UNSIGNED NULL,
    price_type ENUM('fixed', 'pay_what_you_want', 'free') NOT NULL,
    price DECIMAL(10, 2) NULL,
    minimum_price DECIMAL(10, 2) NULL,
    release_notes TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (prompt_id) REFERENCES prompts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_prompt_version (prompt_id, version_number),
    INDEX idx_prompt_id (prompt_id),
    INDEX idx_version_number (version_number),
    INDEX idx_created_at (created_at),
    INDEX idx_uuid (uuid)
);
```

#### Relationships
- Many-to-One with prompts (prompt_id)
- Many-to-One with categories (category_id)
- Many-to-One with users (created_by)

### 3.12 Platform Settings Table

#### Description
Stores platform-wide configuration settings.

#### Schema
```sql
CREATE TABLE platform_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key (key)
);
```

## 4. Indexing Strategy

### 4.1 Primary Keys
All tables use BIGINT UNSIGNED AUTO_INCREMENT primary keys for optimal performance.

### 4.2 Foreign Keys
Foreign key constraints ensure referential integrity and cascade appropriately on deletions.

### 4.3 Performance Indexes
- Composite indexes for common query patterns
- Full-text indexes for search functionality
- UUID indexes for external API references
- Status-based indexes for filtering

### 4.4 Unique Constraints
- Email uniqueness in users table
- Slug uniqueness in categories and tags
- User-prompt uniqueness in purchases and reviews

## 5. Data Integrity

### 5.1 Constraints
- NOT NULL constraints for required fields
- CHECK constraints for data validation
- UNIQUE constraints for business rules
- Foreign key constraints for referential integrity

### 5.2 Default Values
- Sensible defaults for optional fields
- Current timestamps for created_at/updated_at
- Default status values for workflow management

### 5.3 Data Types
- Appropriate data types for each field
- DECIMAL for monetary values
- ENUM for fixed option sets
- JSON for flexible structured data

## 6. Migration Strategy

### 6.1 Versioning
- Sequential migration files with timestamps
- Clear migration descriptions
- Reversible migrations where possible

### 6.2 Deployment
- Automated migration execution
- Pre-deployment validation
- Rollback procedures

### 6.3 Data Seeding
- Initial data seeding for categories
- Test data for development environments
- Production data migration procedures

## 7. Backup and Recovery

### 7.1 Backup Strategy
- Daily full backups
- Hourly incremental backups
- Off-site storage of backups

### 7.2 Recovery Procedures
- Point-in-time recovery
- Table-level recovery
- Disaster recovery testing

## 8. Security Considerations

### 8.1 Data Encryption
- Encryption at rest for sensitive data
- SSL/TLS for data in transit
- Hashing for passwords and tokens

### 8.2 Access Control
- Database user permissions
- Application-level access controls
- Audit logging

---

*This database design document provides the foundation for implementing the data layer of the AI Prompt Marketplace. It ensures data integrity, performance, and scalability while supporting all required business functionality.*