import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Search, 
    Star, 
    ShoppingCart, 
    Eye, 
    Grid3X3, 
    List,
    SlidersHorizontal,
    Sparkles,
    Menu,
    X
} from 'lucide-react';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    children?: Category[];
}

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Seller {
    id: number;
    name: string;
    profile?: {
        avatar: string | null;
        seller_verified: boolean;
    };
}

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    price: number;
    pricing_model: 'fixed' | 'pay_what_you_want' | 'free';
    ai_model: string;
    rating: number;
    rating_count: number;
    views_count: number;
    purchases_count: number;
    featured: boolean;
    category: Category;
    tags: Tag[];
    seller: Seller;
    created_at: string;
}

interface PaginatedPrompts {
    data: Prompt[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

interface AiModel {
    value: string;
    label: string;
}

interface Props {
    prompts: PaginatedPrompts;
    categories: Category[];
    filters: {
        category_id?: string;
        ai_model?: string;
        pricing_model?: string;
        min_price?: string;
        max_price?: string;
        sort_by?: string;
        search?: string;
    };
    aiModels: AiModel[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Marketplace',
        href: '/marketplace',
    },
];

// Public header component for unauthenticated users
function PublicHeader() {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    
    return (
        <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto flex h-16 items-center justify-between px-4">
                <Link href="/" className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <Sparkles className="h-5 w-5" />
                    </div>
                    <span className="text-xl font-bold">CognitPro</span>
                </Link>

                {/* Desktop Navigation */}
                <nav className="hidden md:flex items-center gap-6">
                    <Link href="/marketplace" className="text-sm font-medium text-foreground">
                        Marketplace
                    </Link>
                    <Link href="#categories" className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Categories
                    </Link>
                    <Link href="#featured" className="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                        Featured
                    </Link>
                </nav>

                <div className="hidden md:flex items-center gap-3">
                    <Button variant="ghost" asChild>
                        <Link href="/login">Log in</Link>
                    </Button>
                    <Button asChild>
                        <Link href="/register">Get Started</Link>
                    </Button>
                </div>

                {/* Mobile menu button */}
                <Button
                    variant="ghost"
                    size="icon"
                    className="md:hidden"
                    onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                >
                    {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                </Button>
            </div>

            {/* Mobile Navigation */}
            {mobileMenuOpen && (
                <div className="md:hidden border-t bg-background">
                    <nav className="container mx-auto flex flex-col gap-4 px-4 py-4">
                        <Link href="/marketplace" className="text-sm font-medium">Marketplace</Link>
                        <Link href="#categories" className="text-sm font-medium text-muted-foreground">Categories</Link>
                        <Link href="#featured" className="text-sm font-medium text-muted-foreground">Featured</Link>
                        <div className="flex flex-col gap-2 pt-4 border-t">
                            <Button variant="ghost" asChild className="justify-start">
                                <Link href="/login">Log in</Link>
                            </Button>
                            <Button asChild className="justify-start">
                                <Link href="/register">Get Started</Link>
                            </Button>
                        </div>
                    </nav>
                </div>
            )}
        </header>
    );
}

// Public footer component
function PublicFooter() {
    return (
        <footer className="border-t bg-muted/30 py-8 mt-auto">
            <div className="container mx-auto px-4">
                <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        <div className="flex h-6 w-6 items-center justify-center rounded bg-primary text-primary-foreground">
                            <Sparkles className="h-4 w-4" />
                        </div>
                        <span className="font-semibold">CognitPro</span>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        © {new Date().getFullYear()} CognitPro. All rights reserved.
                    </p>
                    <div className="flex gap-6 text-sm text-muted-foreground">
                        <Link href="#" className="hover:text-foreground transition-colors">Privacy</Link>
                        <Link href="#" className="hover:text-foreground transition-colors">Terms</Link>
                        <Link href="#" className="hover:text-foreground transition-colors">Contact</Link>
                    </div>
                </div>
            </div>
        </footer>
    );
}

export default function MarketplaceIndex({ prompts, categories, filters, aiModels }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isAuthenticated = auth?.user != null;
    
    const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
    const [showFilters, setShowFilters] = useState(false);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [localFilters, setLocalFilters] = useState({
        category_id: filters.category_id || 'all',
        ai_model: filters.ai_model || 'all',
        pricing_model: filters.pricing_model || 'all',
        sort_by: filters.sort_by || 'created_at',
    });

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        applyFilters({ ...localFilters, search: searchTerm });
    };

    const handleFilterChange = (key: string, value: string) => {
        const newFilters = { ...localFilters, [key]: value };
        setLocalFilters(newFilters);
        applyFilters({ ...newFilters, search: searchTerm });
    };

    const applyFilters = (newFilters: Record<string, string>) => {
        // Convert "all" to empty string for API and filter out empty values
        const cleanFilters = Object.fromEntries(
            Object.entries(newFilters)
                .map(([k, v]) => [k, v === 'all' ? '' : v])
                .filter(([, v]) => v !== '')
        );
        router.get('/marketplace', cleanFilters, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setSearchTerm('');
        setLocalFilters({
            category_id: 'all',
            ai_model: 'all',
            pricing_model: 'all',
            sort_by: 'created_at',
        });
        router.get('/marketplace');
    };

    const formatPrice = (price: number | null | undefined, pricingModel: string) => {
        const numericPrice = typeof price === 'number' ? price : 0;
        if (pricingModel === 'free') return 'Free';
        if (pricingModel === 'pay_what_you_want') return `From $${numericPrice.toFixed(2)}`;
        return `$${numericPrice.toFixed(2)}`;
    };

    const renderStars = (rating: number | null | undefined) => {
        const numericRating = typeof rating === 'number' ? rating : 0;
        return (
            <div className="flex items-center gap-1">
                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                <span className="text-sm font-medium">{numericRating.toFixed(1)}</span>
            </div>
        );
    };

    const PromptCard = ({ prompt }: { prompt: Prompt }) => (
        <Card className="group relative overflow-hidden transition-all hover:shadow-lg">
            {prompt.featured && (
                <div className="absolute right-2 top-2 z-10">
                    <Badge variant="default" className="bg-gradient-to-r from-amber-500 to-orange-500">
                        <Sparkles className="mr-1 h-3 w-3" />
                        Featured
                    </Badge>
                </div>
            )}
            <CardHeader className="pb-3">
                <div className="mb-2 flex items-start justify-between gap-2">
                    <Badge variant="outline" className="shrink-0">
                        {prompt.ai_model}
                    </Badge>
                    <Badge variant="secondary">
                        {prompt.category.name}
                    </Badge>
                </div>
                <Link href={`/prompts/${prompt.slug}`}>
                    <CardTitle className="line-clamp-2 text-lg hover:text-primary transition-colors">
                        {prompt.title}
                    </CardTitle>
                </Link>
                <CardDescription className="line-clamp-2">
                    {prompt.description}
                </CardDescription>
            </CardHeader>
            <CardContent className="pb-3">
                <div className="flex flex-wrap gap-1">
                    {prompt.tags.slice(0, 3).map((tag) => (
                        <Badge key={tag.id} variant="outline" className="text-xs">
                            {tag.name}
                        </Badge>
                    ))}
                    {prompt.tags.length > 3 && (
                        <Badge variant="outline" className="text-xs">
                            +{prompt.tags.length - 3}
                        </Badge>
                    )}
                </div>
            </CardContent>
            <CardFooter className="flex items-center justify-between border-t pt-4">
                <div className="flex flex-col">
                    <span className="text-lg font-bold text-primary">
                        {formatPrice(prompt.price, prompt.pricing_model)}
                    </span>
                    <div className="flex items-center gap-3 text-muted-foreground">
                        {renderStars(prompt.rating)}
                        <span className="text-xs">({prompt.rating_count})</span>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <div className="flex items-center gap-1 text-muted-foreground">
                        <Eye className="h-4 w-4" />
                        <span className="text-xs">{prompt.views_count}</span>
                    </div>
                    <div className="flex items-center gap-1 text-muted-foreground">
                        <ShoppingCart className="h-4 w-4" />
                        <span className="text-xs">{prompt.purchases_count}</span>
                    </div>
                </div>
            </CardFooter>
            <div className="border-t px-6 py-3">
                <Link 
                    href={`/sellers/${prompt.seller.id}`}
                    className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors"
                >
                    <div className="h-6 w-6 rounded-full bg-gradient-to-br from-primary/20 to-primary/40 flex items-center justify-center">
                        {prompt.seller.name.charAt(0).toUpperCase()}
                    </div>
                    <span>{prompt.seller.name}</span>
                    {prompt.seller.profile?.seller_verified && (
                        <Badge variant="outline" className="text-xs">Verified</Badge>
                    )}
                </Link>
            </div>
        </Card>
    );

    // Main content that will be wrapped differently based on auth status
    const pageContent = (
        <>
            {/* Hero Section */}
            <section className="border-b bg-gradient-to-b from-muted/50 to-background">
                <div className="container py-12">
                    <div className="mx-auto max-w-3xl text-center">
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                            AI Prompt Marketplace
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Discover premium prompts for GPT-4, Claude, Midjourney, and more. 
                            Quality-assured prompts created by expert prompt engineers.
                        </p>
                    </div>
                    
                    {/* Search Bar */}
                    <form onSubmit={handleSearch} className="mx-auto mt-8 max-w-2xl">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search prompts..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-10 pr-20 h-12"
                            />
                            <Button type="submit" className="absolute right-1 top-1/2 -translate-y-1/2">
                                Search
                            </Button>
                        </div>
                    </form>
                </div>
            </section>

            {/* Main Content */}
            <section className="container py-8">
                <div className="flex flex-col lg:flex-row gap-8">
                    {/* Sidebar Filters */}
                    <aside className={`lg:w-64 shrink-0 ${showFilters ? 'block' : 'hidden lg:block'}`}>
                        <div className="sticky top-8 space-y-6">
                            <div className="flex items-center justify-between">
                                <h3 className="font-semibold">Filters</h3>
                                <Button variant="ghost" size="sm" onClick={clearFilters}>
                                    Clear all
                                </Button>
                            </div>

                            {/* Category Filter */}
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium">Category</h4>
                                <Select
                                    value={localFilters.category_id}
                                    onValueChange={(value) => handleFilterChange('category_id', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All categories</SelectItem>
                                        {categories.map((category) => (
                                            <SelectItem key={category.id} value={category.id.toString()}>
                                                {category.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* AI Model Filter */}
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium">AI Model</h4>
                                <Select
                                    value={localFilters.ai_model}
                                    onValueChange={(value) => handleFilterChange('ai_model', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All models" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All models</SelectItem>
                                        {aiModels.map((model) => (
                                            <SelectItem key={model.value} value={model.value}>
                                                {model.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Pricing Model Filter */}
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium">Pricing</h4>
                                <Select
                                    value={localFilters.pricing_model}
                                    onValueChange={(value) => handleFilterChange('pricing_model', value)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All pricing" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All pricing</SelectItem>
                                        <SelectItem value="free">Free</SelectItem>
                                        <SelectItem value="fixed">Fixed Price</SelectItem>
                                        <SelectItem value="pay_what_you_want">Pay What You Want</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </aside>

                    {/* Prompt Grid */}
                    <div className="flex-1">
                        {/* Toolbar */}
                        <div className="mb-6 flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2">
                                <p className="text-sm text-muted-foreground">
                                    {prompts.total} prompts found
                                </p>
                            </div>
                            <div className="flex items-center gap-4">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="lg:hidden"
                                    onClick={() => setShowFilters(!showFilters)}
                                >
                                    <SlidersHorizontal className="mr-2 h-4 w-4" />
                                    Filters
                                </Button>
                                
                                <Select
                                    value={localFilters.sort_by}
                                    onValueChange={(value) => handleFilterChange('sort_by', value)}
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Sort by" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="created_at">Newest First</SelectItem>
                                        <SelectItem value="popular">Most Popular</SelectItem>
                                        <SelectItem value="rating">Highest Rated</SelectItem>
                                        <SelectItem value="price_low">Price: Low to High</SelectItem>
                                        <SelectItem value="price_high">Price: High to Low</SelectItem>
                                    </SelectContent>
                                </Select>

                                <div className="hidden sm:flex items-center border rounded-md">
                                    <Button
                                        variant={viewMode === 'grid' ? 'secondary' : 'ghost'}
                                        size="icon"
                                        className="rounded-r-none"
                                        onClick={() => setViewMode('grid')}
                                    >
                                        <Grid3X3 className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant={viewMode === 'list' ? 'secondary' : 'ghost'}
                                        size="icon"
                                        className="rounded-l-none"
                                        onClick={() => setViewMode('list')}
                                    >
                                        <List className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        {/* Prompts Grid */}
                        {prompts.data.length > 0 ? (
                            <>
                                <div className={viewMode === 'grid' 
                                    ? "grid gap-6 sm:grid-cols-2 lg:grid-cols-3" 
                                    : "space-y-4"
                                }>
                                    {prompts.data.map((prompt) => (
                                        <PromptCard key={prompt.id} prompt={prompt} />
                                    ))}
                                </div>

                                {/* Pagination */}
                                {prompts.last_page > 1 && (
                                    <div className="mt-8 flex justify-center gap-2">
                                        {prompts.links.prev && (
                                            <Button
                                                variant="outline"
                                                onClick={() => router.get(prompts.links.prev!)}
                                            >
                                                Previous
                                            </Button>
                                        )}
                                        <span className="flex items-center px-4 text-sm text-muted-foreground">
                                            Page {prompts.current_page} of {prompts.last_page}
                                        </span>
                                        {prompts.links.next && (
                                            <Button
                                                variant="outline"
                                                onClick={() => router.get(prompts.links.next!)}
                                            >
                                                Next
                                            </Button>
                                        )}
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="text-center py-12">
                                <div className="mx-auto w-24 h-24 rounded-full bg-muted flex items-center justify-center mb-4">
                                    <Search className="h-10 w-10 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold">No prompts found</h3>
                                <p className="text-muted-foreground mt-1">
                                    Try adjusting your search or filters
                                </p>
                                <Button onClick={clearFilters} className="mt-4">
                                    Clear Filters
                                </Button>
                            </div>
                        )}
                    </div>
                </div>
            </section>
        </>
    );

    // For authenticated users: use AppLayout with sidebar
    if (isAuthenticated) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="AI Prompt Marketplace" />
                {pageContent}
            </AppLayout>
        );
    }

    // For unauthenticated users: use public layout without sidebar
    return (
        <div className="flex min-h-screen flex-col bg-background">
            <Head title="AI Prompt Marketplace - CognitPro" />
            <PublicHeader />
            <main className="flex-1">
                {pageContent}
            </main>
            <PublicFooter />
        </div>
    );
}
