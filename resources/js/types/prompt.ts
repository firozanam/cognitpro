/**
 * Canonical marketplace domain shapes shared across pages.
 * Fields marked optional are not present on every surface — pages
 * may rely only on the fields their backend actually delivers.
 */

export interface Seller {
    id: number;
    name: string;
    email?: string;
    avatar?: string | null;
    profile?: {
        avatar: string | null;
        seller_verified?: boolean;
        total_sales?: number;
        bio?: string | null;
    };
}

export interface Category {
    id: number;
    name: string;
    slug: string;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
}

export type PricingModel = 'fixed' | 'pay_what_you_want' | 'free';

export type PromptStatus = 'draft' | 'pending' | 'approved' | 'rejected';

export interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    /** Present only on entitled/owner views (hidden on listings). */
    content?: string;
    ai_model: string;
    price: number | string;
    pricing_model: PricingModel | string;
    min_price?: number | string | null;
    rating: number;
    rating_count: number;
    views_count: number;
    purchases_count: number;
    featured: boolean;
    status?: PromptStatus | string;
    rejection_reason?: string | null;
    version?: number;
    created_at: string;
    updated_at?: string;
    category: Category;
    tags: Tag[];
    seller: Seller;
    reviews?: Review[];
}

export interface Review {
    id: number;
    rating: number;
    title: string | null;
    content: string | null;
    helpful_count: number;
    seller_response: string | null;
    seller_responded_at: string | null;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
    prompt?: Pick<Prompt, 'id' | 'title' | 'slug'> & {
        seller: Pick<Seller, 'id' | 'name'>;
    };
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page?: number;
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}
