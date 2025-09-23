# CognitPro AI Prompt Marketplace - UI/UX Design Document

## 1. Introduction

This document provides a comprehensive overview of the UI/UX design for the AI Prompt Marketplace. It details the user interface components, user flows, design principles, and implementation guidelines for creating an intuitive and engaging platform for both prompt engineers (sellers) and customers (buyers).

## 2. Design Principles

### 2.1 User-Centered Design
- Focus on the needs of both buyers and sellers
- Prioritize ease of use and intuitive navigation
- Ensure accessibility compliance (WCAG 2.1 AA)

### 2.2 Consistency
- Maintain consistent design patterns throughout the platform
- Use standardized components from ShadCN UI
- Follow established interaction patterns

### 2.3 Responsiveness
- Mobile-first design approach
- Adaptive layouts for all device sizes
- Touch-friendly interactions

### 2.4 Performance
- Optimize for fast loading times
- Implement lazy loading where appropriate
- Minimize cognitive load

## 3. Design System

### 3.1 Color Palette
- Primary: #3B82F6 (Blue for trust and professionalism)
- Secondary: #10B981 (Green for growth and success)
- Accent: #8B5CF6 (Purple for creativity and innovation)
- Neutral: #F3F4F6 (Light gray for backgrounds)
- Text: #1F2937 (Dark gray for primary text)
- Error: #EF4444 (Red for errors and warnings)

### 3.2 Typography
- Primary: Inter (Modern, clean sans-serif)
- Heading hierarchy:
  - H1: 36px, Bold
  - H2: 28px, Semi-bold
  - H3: 22px, Semi-bold
  - H4: 18px, Medium
  - Body: 16px, Regular
  - Caption: 14px, Regular

### 3.3 Spacing
- Base unit: 8px
- XS: 4px
- S: 8px
- M: 16px
- L: 24px
- XL: 32px
- XXL: 48px

### 3.4 Component Library
Using ShadCN UI components with custom styling:
- Buttons (Primary, Secondary, Destructive)
- Input fields (Text, Select, Textarea)
- Cards (Prompt cards, Category cards)
- Navigation (Header, Sidebar, Footer)
- Modals and Dialogs
- Forms with validation
- Data tables and lists

## 4. User Flows

### 4.1 Buyer User Flow

#### 4.1.1 Registration and Onboarding
1. Landing page with value proposition
2. Registration form (email, password, name)
3. Email verification
4. Role selection (buyer/seller)
5. Welcome tour and onboarding

#### 4.1.2 Prompt Discovery
1. Homepage with featured prompts
2. Category browsing
3. Search with filters (price, rating, category, tags)
4. Prompt listing page with sorting options
5. Prompt detail page

#### 4.1.3 Purchase Process
1. Prompt detail page with purchase option
2. Payment method selection
3. Payment processing
4. Purchase confirmation
5. Access to prompt content

#### 4.1.4 Review Process
1. Access to purchased prompt
2. Review modal/form
3. Rating selection (1-5 stars)
4. Review text input
5. Review submission

### 4.2 Seller User Flow

#### 4.2.1 Registration and Onboarding
1. Landing page with seller value proposition
2. Registration form with seller-specific information
3. Email verification
4. Profile setup (bio, payment information)
5. Seller onboarding tour

#### 4.2.2 Prompt Creation
1. Dashboard with "Create Prompt" button
2. Prompt creation form:
   - Title and description
   - Full prompt content
   - Category and tags selection
   - Pricing configuration
3. Preview functionality
4. Save as draft or publish

#### 4.2.3 Prompt Management
1. Dashboard with prompt listing
2. Prompt status management (draft, published, archived)
3. Prompt editing
4. Performance analytics

#### 4.2.4 Sales and Payout Management
1. Sales dashboard with metrics
2. Purchase history
3. Payout information and history
4. Tax document access

### 4.3 Admin User Flow

#### 4.3.1 User Management
1. Admin dashboard
2. User listing with filtering
3. User detail view
4. Role assignment/modification
5. Account suspension/banning

#### 4.3.2 Content Moderation
1. Flagged content dashboard
2. Prompt review interface
3. Review moderation
4. Content approval/rejection

#### 4.3.3 Platform Configuration
1. Settings management
2. Commission rate configuration
3. Payment gateway setup
4. Platform announcement system

## 5. Key Pages and Components

### 5.1 Homepage

#### Components
- Hero section with value proposition
- Featured prompts carousel
- Category browsing cards
- Trending prompts section
- Call-to-action for sellers to join
- Testimonials/reviews section

#### Layout
```
+-----------------------------------------------------+
| Header (Logo, Navigation, Auth/User Menu)           |
+-----------------------------------------------------+
| Hero Section                                        |
|  - Headline                                         |
|  - Subheadline                                      |
|  - Primary CTA (Browse Prompts)                     |
|  - Secondary CTA (Sell Prompts)                     |
+-----------------------------------------------------+
| Featured Prompts Carousel                           |
+-----------------------------------------------------+
| Category Browsing                                   |
|  [Category Card] [Category Card] [Category Card]    |
+-----------------------------------------------------+
| Trending Prompts                                    |
|  [Prompt Card] [Prompt Card] [Prompt Card]          |
+-----------------------------------------------------+
| Testimonials                                        |
+-----------------------------------------------------+
| Footer (Links, Social, Copyright)                   |
+-----------------------------------------------------+
```

### 5.2 Prompt Listing Page

#### Components
- Search bar with filters
- Sort controls
- Prompt cards grid
- Pagination controls
- Category breadcrumb

#### Layout
```
+-----------------------------------------------------+
| Header                                              |
+-----------------------------------------------------+
| Breadcrumb                                          |
+-----------------------------------------------------+
| Search and Filters                                  |
|  [Search Input] [Category Filter] [Price Filter]    |
|  [Rating Filter] [Sort Dropdown]                    |
+-----------------------------------------------------+
| Prompt Grid                                         |
|  [Prompt Card] [Prompt Card] [Prompt Card]          |
|  [Prompt Card] [Prompt Card] [Prompt Card]          |
+-----------------------------------------------------+
| Pagination                                          |
+-----------------------------------------------------+
| Footer                                              |
+-----------------------------------------------------+
```

### 5.3 Prompt Detail Page

#### Components
- Prompt header (title, seller info, rating)
- Prompt description
- Pricing information
- Purchase button
- Preview content (for free prompts)
- Reviews section
- Related prompts
- Version history toggle/section

#### Layout
```
+-----------------------------------------------------+
| Header                                              |
+-----------------------------------------------------+
| Breadcrumb                                          |
+-----------------------------------------------------+
| Prompt Header                                       |
|  Title, Seller, Rating, Price                       |
+-----------------------------------------------------+
| Prompt Description                                  |
+-----------------------------------------------------+
| [Purchase Button]                                   |
+-----------------------------------------------------+
| Preview Content (if applicable)                     |
+-----------------------------------------------------+
| Reviews Section                                     |
|  Review Summary                                     |
|  [Review Card] [Review Card]                        |
|  [Write Review Form]                                |
+-----------------------------------------------------+
| Related Prompts                                     |
|  [Prompt Card] [Prompt Card] [Prompt Card]          |
+-----------------------------------------------------+
| Footer                                              |
+-----------------------------------------------------+
```

### 5.4 Seller Dashboard

#### Components
- Dashboard overview with metrics
- Prompt management interface
- Sales analytics
- Payout information
- Profile settings

#### Layout
```
+-----------------------------------------------------+
| Header                                              |
+-----------------------------------------------------+
| Sidebar Navigation                                  |
|  Dashboard                                          |
|  My Prompts                                         |
|  Analytics                                          |
|  Payouts                                            |
|  Settings                                           |
+-----------------------------------------------------+
| Main Content Area                                   |
|  Dashboard Overview                                 |
|   - Total Sales                                     |
|   - Total Prompts                                   |
|   - Recent Activity                                 |
|  Quick Actions                                      |
|   [Create Prompt] [View Analytics] [View Payouts]   |
+-----------------------------------------------------+
| Footer                                              |
+-----------------------------------------------------+
```

### 5.5 Prompt Creation/Edit Page

#### Components
- Form wizard or single page form
- Title and description fields
- Rich text editor for prompt content
- Category and tag selection
- Pricing configuration
- Preview functionality
- Version release notes input
- Save/Publish controls

#### Layout
```
+-----------------------------------------------------+
| Header                                              |
+-----------------------------------------------------+
| Form Navigation (Steps)                             |
+-----------------------------------------------------+
| Prompt Information                                  |
|  [Title Input]                                      |
|  [Description Textarea]                             |
|  [Category Select]                                  |
|  [Tags Input]                                       |
+-----------------------------------------------------+
| Prompt Content                                      |
|  [Rich Text Editor]                                 |
+-----------------------------------------------------+
| Pricing Configuration                               |
|  [Price Type Selector]                              |
|  [Price Input (if applicable)]                      |
+-----------------------------------------------------+
| Preview                                             |
|  [Preview Button]                                   |
|  [Preview Content]                                  |
+-----------------------------------------------------+
| Actions                                             |
|  [Save Draft] [Publish] [Cancel]                    |
+-----------------------------------------------------+
| Footer                                              |
+-----------------------------------------------------+
```

### 5.5 Prompt Version History Component

#### Components
- Version list with version numbers and timestamps
- Release notes display
- Diff visualization between versions
- Rollback functionality for prompt owners
- Version comparison toggle

#### Layout
```
+-----------------------------------------------------+
| Version History Section                             |
|  [Expand/Collapse Toggle]                           |
+-----------------------------------------------------+
| Version List                                        |
|  [Version 3] - Feb 5, 2023 - Major improvements     |
|  [Version 2] - Jan 25, 2023 - Bug fixes             |
|  [Version 1] - Jan 15, 2023 - Initial release       |
+-----------------------------------------------------+
| Version Detail (when selected)                      |
|  Release Notes: Major improvements with new examples|
|  [View Diff] [Rollback to this version]             |
+-----------------------------------------------------+
```

### 5.6 User Profile Page

#### Components
- User information header
- Bio and social links
- Prompt portfolio (for sellers)
- Purchase history (for buyers)
- Review history
- Edit profile button

#### Layout
```
+-----------------------------------------------------+
| Header                                              |
+-----------------------------------------------------+
| User Header                                         |
|  Profile Photo, Name, Role, Rating                  |
|  [Edit Profile Button]                              |
+-----------------------------------------------------+
| User Bio and Info                                   |
|  Bio, Location, Website, Member Since               |
+-----------------------------------------------------+
| Content Tabs                                        |
|  Prompts | Purchases | Reviews                      |
|                                                     |
|  [Prompt Card Grid]                                 |
|  OR                                                 |
|  [Purchase History List]                            |
|  OR                                                 |
|  [Review List]                                      |
+-----------------------------------------------------+
| Footer                                              |
+-----------------------------------------------------+
```

## 6. Component Specifications

### 6.1 Prompt Card Component

#### Props
```typescript
interface PromptCardProps {
  id: number;
  title: string;
  description: string;
  price: number;
  priceType: 'fixed' | 'pay_what_you_want' | 'free';
  category: {
    id: number;
    name: string;
  };
  seller: {
    id: number;
    name: string;
  };
  rating: number;
  reviewCount: number;
  purchaseCount: number;
  isPurchased: boolean;
  createdAt: string;
}
```

#### Features
- Title and description truncation
- Price display with appropriate labeling
- Category and seller information
- Rating display with review count
- Purchase count indicator
- "Purchased" badge for owned prompts
- Responsive design for grid layouts

### 6.2 Review Card Component

#### Props
```typescript
interface ReviewCardProps {
  id: number;
  user: {
    id: number;
    name: string;
    avatar?: string;
  };
  rating: number;
  title: string;
  reviewText: string;
  verifiedPurchase: boolean;
  helpfulCount: number;
  createdAt: string;
}
```

#### Features
- Star rating display
- User information with avatar
- Review title and text
- "Verified Purchase" badge
- Helpful count with voting
- Relative time display

### 6.3 Category Card Component

#### Props
```typescript
interface CategoryCardProps {
  id: number;
  name: string;
  description: string;
  promptCount: number;
  icon: string;
  color: string;
}
```

#### Features
- Icon with color coding
- Category name and description
- Prompt count display
- Hover effects for interaction
- Link to category prompt listing

### 6.4 Search and Filter Component

#### Props
```typescript
interface SearchFilterProps {
  onSearch: (query: string) => void;
  onFilterChange: (filters: FilterOptions) => void;
  categories: Category[];
  defaultFilters?: FilterOptions;
}
```

#### Features
- Search input with debounce
- Category filter dropdown
- Price range slider
- Rating filter
- Clear filters button
- Responsive collapse for mobile

### 6.5 Prompt Version History Component

#### Props
```typescript
interface VersionHistoryProps {
  promptId: number;
  versions: PromptVersion[];
  currentVersion: number;
  onVersionSelect: (versionId: number) => void;
  onRollback: (versionId: number) => void;
  canEdit: boolean;
}

interface PromptVersion {
  id: number;
  versionNumber: number;
  title: string;
  releaseNotes: string;
  createdBy: {
    id: number;
    name: string;
  };
  createdAt: string;
}
```

#### Features
- Expandable/collapsible section
- Chronological version list
- Release notes display
- Current version highlighting
- Rollback functionality for owners
- Diff visualization (link to separate view)

### 6.6 Navigation Components

#### Header Component
- Logo linking to homepage
- Primary navigation links
- Search bar (desktop)
- User menu with authentication state
- Mobile menu toggle

#### Sidebar Component (Dashboard)
- Navigation links with icons
- Active state highlighting
- Collapsible sections
- User profile summary

#### Footer Component
- Site links and navigation
- Social media links
- Newsletter signup
- Copyright and legal information

## 7. Responsive Design

### 7.1 Breakpoints
- Mobile: 0px - 768px
- Tablet: 769px - 1024px
- Desktop: 1025px - 1200px
- Large Desktop: 1201px+

### 7.2 Mobile-First Approach
- Stacked layouts for mobile
- Collapsible navigation menus
- Touch-friendly button sizes
- Simplified filter interactions
- Optimized form layouts

### 7.3 Adaptive Components
- Grid layouts that adapt to screen size
- Font sizes that scale appropriately
- Touch targets with minimum 44px size
- Condensed layouts for smaller screens

## 8. Accessibility

### 8.1 WCAG 2.1 AA Compliance
- Proper color contrast ratios
- Keyboard navigation support
- Screen reader compatibility
- Focus management
- ARIA labels and roles

### 8.2 Semantic HTML
- Proper heading hierarchy
- Meaningful link text
- Form labeling
- Landmark regions

### 8.3 Keyboard Navigation
- Tab order optimization
- Skip to content link
- Focus indicators
- Keyboard shortcuts

## 9. Performance Considerations

### 9.1 Loading States
- Skeleton screens for content
- Progress indicators for actions
- Optimistic UI updates
- Error states with retry options

### 9.2 Image Optimization
- Responsive images with srcset
- Lazy loading for off-screen content
- Appropriate image formats
- Compression and optimization

### 9.3 Code Splitting
- Route-based code splitting
- Component lazy loading
- Dynamic imports for heavy features

## 10. Implementation Guidelines

### 10.1 Component Development
- Reusable, modular components
- TypeScript interfaces for props
- Storybook for component documentation
- Unit tests for components

### 10.2 State Management
- Redux Toolkit for global state
- React hooks for local state
- Async thunks for API calls
- Proper state normalization

### 10.3 API Integration
- Axios for HTTP requests
- Request/response interceptors
- Error handling and logging
- Loading state management

### 10.4 Testing Strategy
- Component unit tests with React Testing Library
- Integration tests for user flows
- End-to-end tests with Cypress
- Accessibility testing

## 11. Future Enhancements

### 11.1 Advanced Features
- Dark mode toggle
- Language localization
- Advanced search with AI-powered suggestions
- Prompt testing environment
- Team collaboration features

### 11.2 Mobile Application
- Native mobile apps for iOS and Android
- Offline access to purchased prompts
- Push notifications
- Biometric authentication

---

*This UI/UX design document provides a comprehensive overview of the user interface and experience for the AI Prompt Marketplace. It will be updated as new features are added or design improvements are made.*