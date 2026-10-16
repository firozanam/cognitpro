import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import PublicLayout from '@/layouts/public-layout';
import { formatPromptPrice } from '@/lib/format';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Paginated, type Prompt } from '@/types/prompt';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    Eye,
    Grid3X3,
    List,
    Search,
    ShoppingCart,
    SlidersHorizontal,
    Sparkles,
    Star,
} from 'lucide-react';
import { useState } from 'react';

interface CategoryNode {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    children?: CategoryNode[];
}

interface AiModel {
    value: string;
    label: string;
}

interface Props {
    prompts: Paginated<Prompt>;
    categories: CategoryNode[];
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

export default function MarketplaceIndex({
    prompts,
    categories,
    filters,
    aiModels,
}: Props) {
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
                .filter(([, v]) => v !== ''),
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

    const renderStars = (rating: number | null | undefined) => {
        const numericRating = typeof rating === 'number' ? rating : 0;
        return (
            <div className="flex items-center gap-1">
                <Star className="h-4 w-4 fill-warning text-warning" />
                <span className="text-sm font-medium">
                    {numericRating.toFixed(1)}
                </span>
            </div>
        );
    };

    const PromptCard = ({ prompt }: { prompt: Prompt }) => (
        <Card className="group relative overflow-hidden transition-all hover:shadow-lg">
            {prompt.featured && (
                <div className="absolute top-0 right-0 flex h-12 w-12 items-start justify-end bg-gradient-to-br from-amber-500 to-orange-500 p-2.5 [clip-path:polygon(0_0,100%_0,100%_100%)]">
                    <Sparkles className="h-3 w-3 text-white" />
                    <span className="sr-only">Featured</span>
                </div>
            )}
            <CardHeader className="pb-3">
                <div className="mb-2 flex flex-wrap items-center gap-2">
                    <Badge variant="outline" className="shrink-0">
                        {prompt.ai_model}
                    </Badge>
                    <Badge variant="secondary" className="ml-auto">
                        {prompt.category.name}
                    </Badge>
                </div>
                <Link href={`/prompts/${prompt.slug}`}>
                    <CardTitle className="line-clamp-2 text-lg transition-colors hover:text-primary">
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
                        <Badge
                            key={tag.id}
                            variant="outline"
                            className="text-xs"
                        >
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
                    {prompt.pricing_model === 'pay_what_you_want' ? (
                        <span className="text-sm font-semibold text-primary">
                            Pay what you want
                        </span>
                    ) : (
                        <span className="text-lg font-bold text-primary">
                            {formatPromptPrice(prompt)}
                        </span>
                    )}
                    <div className="flex items-center gap-3 text-muted-foreground">
                        {renderStars(prompt.rating)}
                        <span className="text-xs">({prompt.rating_count})</span>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            router.post(`/cart/add/${prompt.id}`)
                        }
                    >
                        <ShoppingCart className="mr-1 h-4 w-4" />
                        Add
                    </Button>
                    <div className="flex items-center gap-1 text-muted-foreground">
                        <Eye className="h-4 w-4" />
                        <span className="text-xs">{prompt.views_count}</span>
                    </div>
                    <div className="flex items-center gap-1 text-muted-foreground">
                        <ShoppingCart className="h-4 w-4" />
                        <span className="text-xs">
                            {prompt.purchases_count}
                        </span>
                    </div>
                </div>
            </CardFooter>
            <div className="border-t px-6 py-3">
                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <div className="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/40">
                        {prompt.seller.name.charAt(0).toUpperCase()}
                    </div>
                    <span>{prompt.seller.name}</span>
                    {prompt.seller.profile?.seller_verified && (
                        <Badge variant="outline" className="text-xs">
                            Verified
                        </Badge>
                    )}
                </div>
            </div>
        </Card>
    );

    // Main content that will be wrapped differently based on auth status
    const pageContent = (
        <>
            {/* Hero Section */}
            <section className="border-b bg-gradient-to-b from-muted/50 to-background">
                <div className="container mx-auto px-6 py-12">
                    <div className="mx-auto max-w-3xl text-center">
                        <h1 className="text-4xl font-bold tracking-tight sm:text-5xl">
                            AI Prompt Marketplace
                        </h1>
                        <p className="mt-4 text-lg text-muted-foreground">
                            Discover premium prompts for GPT-4, Claude,
                            Midjourney, and more. Quality-assured prompts
                            created by expert prompt engineers.
                        </p>
                    </div>

                    {/* Search Bar */}
                    <form
                        onSubmit={handleSearch}
                        className="mx-auto mt-8 max-w-2xl"
                    >
                        <div className="relative">
                            <Search className="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search prompts..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-12 pr-20 pl-10"
                            />
                            <Button
                                type="submit"
                                className="absolute top-1/2 right-1 -translate-y-1/2"
                            >
                                Search
                            </Button>
                        </div>
                    </form>
                </div>
            </section>

            {/* Main Content */}
            <section className="container mx-auto px-6 py-8">
                <div className="flex flex-col gap-8 lg:flex-row">
                    {/* Sidebar Filters */}
                    <aside
                        className={`shrink-0 lg:w-64 ${showFilters ? 'block' : 'hidden lg:block'}`}
                    >
                        <div className="sticky top-8 space-y-6">
                            <div className="flex items-center justify-between">
                                <h3 className="font-semibold">Filters</h3>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={clearFilters}
                                >
                                    Clear all
                                </Button>
                            </div>

                            {/* Category Filter */}
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium">
                                    Category
                                </h4>
                                <Select
                                    value={localFilters.category_id}
                                    onValueChange={(value) =>
                                        handleFilterChange('category_id', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All categories" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All categories
                                        </SelectItem>
                                        {categories.map((category) => (
                                            <SelectItem
                                                key={category.id}
                                                value={category.id.toString()}
                                            >
                                                {category.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* AI Model Filter */}
                            <div className="space-y-2">
                                <h4 className="text-sm font-medium">
                                    AI Model
                                </h4>
                                <Select
                                    value={localFilters.ai_model}
                                    onValueChange={(value) =>
                                        handleFilterChange('ai_model', value)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All models" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All models
                                        </SelectItem>
                                        {aiModels.map((model) => (
                                            <SelectItem
                                                key={model.value}
                                                value={model.value}
                                            >
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
                                    onValueChange={(value) =>
                                        handleFilterChange(
                                            'pricing_model',
                                            value,
                                        )
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="All pricing" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">
                                            All pricing
                                        </SelectItem>
                                        <SelectItem value="free">
                                            Free
                                        </SelectItem>
                                        <SelectItem value="fixed">
                                            Fixed Price
                                        </SelectItem>
                                        <SelectItem value="pay_what_you_want">
                                            Pay What You Want
                                        </SelectItem>
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
                                    onValueChange={(value) =>
                                        handleFilterChange('sort_by', value)
                                    }
                                >
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Sort by" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="created_at">
                                            Newest First
                                        </SelectItem>
                                        <SelectItem value="popular">
                                            Most Popular
                                        </SelectItem>
                                        <SelectItem value="rating">
                                            Highest Rated
                                        </SelectItem>
                                        <SelectItem value="price_low">
                                            Price: Low to High
                                        </SelectItem>
                                        <SelectItem value="price_high">
                                            Price: High to Low
                                        </SelectItem>
                                    </SelectContent>
                                </Select>

                                <div className="hidden items-center rounded-md border sm:flex">
                                    <Button
                                        variant={
                                            viewMode === 'grid'
                                                ? 'secondary'
                                                : 'ghost'
                                        }
                                        size="icon"
                                        className="rounded-r-none"
                                        onClick={() => setViewMode('grid')}
                                    >
                                        <Grid3X3 className="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant={
                                            viewMode === 'list'
                                                ? 'secondary'
                                                : 'ghost'
                                        }
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
                                <div
                                    className={
                                        viewMode === 'grid'
                                            ? 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3'
                                            : 'space-y-4'
                                    }
                                >
                                    {prompts.data.map((prompt) => (
                                        <PromptCard
                                            key={prompt.id}
                                            prompt={prompt}
                                        />
                                    ))}
                                </div>

                                {/* Pagination */}
                                {prompts.last_page > 1 && (
                                    <div className="mt-8 flex justify-center gap-2">
                                        {prompts.prev_page_url && (
                                            <Button
                                                variant="outline"
                                                onClick={() =>
                                                    router.get(
                                                        prompts.prev_page_url!,
                                                    )
                                                }
                                            >
                                                Previous
                                            </Button>
                                        )}
                                        <span className="flex items-center px-4 text-sm text-muted-foreground">
                                            Page {prompts.current_page} of{' '}
                                            {prompts.last_page}
                                        </span>
                                        {prompts.next_page_url && (
                                            <Button
                                                variant="outline"
                                                onClick={() =>
                                                    router.get(
                                                        prompts.next_page_url!,
                                                    )
                                                }
                                            >
                                                Next
                                            </Button>
                                        )}
                                    </div>
                                )}
                            </>
                        ) : (
                            <div className="py-12 text-center">
                                <div className="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-muted">
                                    <Search className="h-10 w-10 text-muted-foreground" />
                                </div>
                                <h3 className="text-lg font-semibold">
                                    No prompts found
                                </h3>
                                <p className="mt-1 text-muted-foreground">
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

    // Authenticated users get the sidebar layout; guests get the public layout
    if (isAuthenticated) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="AI Prompt Marketplace" />
                {pageContent}
            </AppLayout>
        );
    }

    return (
        <PublicLayout
            navLinks={[
                { label: 'Marketplace', href: '/marketplace', active: true },
                { label: 'Categories', href: '#categories' },
                { label: 'Featured', href: '#featured' },
            ]}
        >
            <Head title="AI Prompt Marketplace - CognitPro" />
            {pageContent}
        </PublicLayout>
    );
}
