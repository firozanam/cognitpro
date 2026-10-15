import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    Check,
    ChevronLeft,
    Clock,
    Copy,
    Eye,
    ShoppingCart,
    Star,
    Tag as TagIcon,
} from 'lucide-react';
import { useState } from 'react';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Seller {
    id: number;
    name: string;
    email: string;
    profile?: {
        avatar: string | null;
        bio: string | null;
        seller_verified: boolean;
        total_sales: number;
    };
}

interface Review {
    id: number;
    rating: number;
    title: string | null;
    content: string | null;
    helpful_count: number;
    created_at: string;
    user: {
        id: number;
        name: string;
    };
    seller_response: string | null;
    seller_responded_at: string | null;
}

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    content: string;
    price: number;
    pricing_model: 'fixed' | 'pay_what_you_want' | 'free';
    min_price: number | null;
    ai_model: string;
    version: number;
    rating: number;
    rating_count: number;
    views_count: number;
    purchases_count: number;
    featured: boolean;
    status: string;
    created_at: string;
    updated_at: string;
    category: Category;
    tags: Tag[];
    seller: Seller;
    reviews: Review[];
}

interface Props {
    prompt: Prompt;
    showContent: boolean;
    hasPurchased: boolean;
}

export default function PromptShow({
    prompt,
    showContent,
    hasPurchased,
}: Props) {
    const [copied, setCopied] = useState(false);
    const { auth } = usePage<SharedData>().props ?? {};

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Marketplace',
            href: '/marketplace',
        },
        {
            title: prompt.category.name,
            href: `/marketplace?category=${prompt.category.id}`,
        },
        {
            title: prompt.title,
            href: `/prompts/${prompt.slug}`,
        },
    ];

    const formatPrice = (
        price: number | null | undefined,
        pricingModel: string,
    ) => {
        const numericPrice = typeof price === 'number' ? price : 0;
        if (pricingModel === 'free') return 'Free';
        if (pricingModel === 'pay_what_you_want')
            return `From $${numericPrice.toFixed(2)}`;
        return `$${numericPrice.toFixed(2)}`;
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const copyToClipboard = () => {
        navigator.clipboard.writeText(prompt.content);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const handlePurchase = () => {
        if (!auth?.user) {
            router.visit('/login');
            return;
        }

        router.visit(`/checkout/${prompt.id}`);
    };

    const handleAddToCart = () => {
        if (!auth?.user) {
            router.visit('/login');
            return;
        }

        router.post(`/cart/add/${prompt.id}`);
    };

    const renderStars = (rating: number, size: 'sm' | 'md' = 'sm') => {
        const starSize = size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
        return (
            <div className="flex items-center gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        className={`${starSize} ${
                            star <= Math.round(rating)
                                ? 'fill-yellow-400 text-yellow-400'
                                : 'fill-none text-muted-foreground'
                        }`}
                    />
                ))}
            </div>
        );
    };

    const isOwner = auth?.user?.id === prompt.seller.id;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={prompt.title} />

            <div className="container mx-auto px-6 py-8">
                {/* Back Button */}
                <Link
                    href="/marketplace"
                    className="mb-6 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                >
                    <ChevronLeft className="mr-1 h-4 w-4" />
                    Back to Marketplace
                </Link>

                <div className="grid gap-8 lg:grid-cols-3">
                    {/* Main Content */}
                    <div className="space-y-6 lg:col-span-2">
                        {/* Header */}
                        <div>
                            <div className="mb-4 flex items-start justify-between gap-4">
                                <div className="flex flex-wrap gap-2">
                                    <Badge variant="outline">
                                        {prompt.ai_model}
                                    </Badge>
                                    <Badge variant="secondary">
                                        {prompt.category.name}
                                    </Badge>
                                    {prompt.featured && (
                                        <Badge className="bg-gradient-to-r from-amber-500 to-orange-500">
                                            Featured
                                        </Badge>
                                    )}
                                </div>
                            </div>

                            <h1 className="mb-4 text-3xl font-bold tracking-tight">
                                {prompt.title}
                            </h1>

                            <p className="text-lg text-muted-foreground">
                                {prompt.description}
                            </p>
                        </div>

                        {/* Tags */}
                        {prompt.tags.length > 0 && (
                            <div className="flex flex-wrap gap-2">
                                <TagIcon className="h-4 w-4 self-center text-muted-foreground" />
                                {prompt.tags.map((tag) => (
                                    <Link
                                        key={tag.id}
                                        href={`/marketplace?tag=${tag.slug}`}
                                    >
                                        <Badge
                                            variant="outline"
                                            className="hover:bg-accent"
                                        >
                                            {tag.name}
                                        </Badge>
                                    </Link>
                                ))}
                            </div>
                        )}

                        {/* Prompt Content */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Prompt</span>
                                    {showContent && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={copyToClipboard}
                                        >
                                            {copied ? (
                                                <>
                                                    <Check className="mr-2 h-4 w-4" />
                                                    Copied!
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="mr-2 h-4 w-4" />
                                                    Copy
                                                </>
                                            )}
                                        </Button>
                                    )}
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {showContent ? (
                                    <pre className="overflow-x-auto rounded-lg bg-muted p-4 font-mono text-sm whitespace-pre-wrap">
                                        {prompt.content}
                                    </pre>
                                ) : (
                                    <div className="relative">
                                        <pre className="max-h-48 overflow-hidden rounded-lg bg-muted p-4 font-mono text-sm whitespace-pre-wrap blur-sm">
                                            {prompt.content}
                                        </pre>
                                        <div className="absolute inset-0 flex items-center justify-center bg-background/80">
                                            <div className="text-center">
                                                <AlertCircle className="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
                                                <p className="mb-2 text-muted-foreground">
                                                    Purchase to view the full
                                                    prompt
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Reviews Section */}
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Reviews ({prompt.rating_count})
                                </CardTitle>
                                <CardDescription>
                                    What buyers are saying about this prompt
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {prompt.reviews.length > 0 ? (
                                    <div className="space-y-6">
                                        {prompt.reviews
                                            .slice(0, 5)
                                            .map((review) => (
                                                <div
                                                    key={review.id}
                                                    className="space-y-3"
                                                >
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex items-center gap-3">
                                                            <Avatar className="h-8 w-8">
                                                                <AvatarFallback>
                                                                    {review.user.name.charAt(
                                                                        0,
                                                                    )}
                                                                </AvatarFallback>
                                                            </Avatar>
                                                            <div>
                                                                <p className="font-medium">
                                                                    {
                                                                        review
                                                                            .user
                                                                            .name
                                                                    }
                                                                </p>
                                                                <div className="flex items-center gap-2">
                                                                    {renderStars(
                                                                        review.rating,
                                                                    )}
                                                                    <span className="text-xs text-muted-foreground">
                                                                        {formatDate(
                                                                            review.created_at,
                                                                        )}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div className="flex items-center gap-1 text-muted-foreground">
                                                            <span className="text-xs">
                                                                {
                                                                    review.helpful_count
                                                                }
                                                            </span>
                                                            <span className="text-xs">
                                                                helpful
                                                            </span>
                                                        </div>
                                                    </div>
                                                    {review.title && (
                                                        <p className="font-medium">
                                                            {review.title}
                                                        </p>
                                                    )}
                                                    {review.content && (
                                                        <p className="text-muted-foreground">
                                                            {review.content}
                                                        </p>
                                                    )}
                                                    {review.seller_response && (
                                                        <div className="ml-6 rounded-lg bg-muted p-3">
                                                            <p className="mb-1 text-sm font-medium">
                                                                Seller Response
                                                            </p>
                                                            <p className="text-sm text-muted-foreground">
                                                                {
                                                                    review.seller_response
                                                                }
                                                            </p>
                                                        </div>
                                                    )}
                                                    <Separator />
                                                </div>
                                            ))}
                                    </div>
                                ) : (
                                    <div className="py-8 text-center">
                                        <p className="text-muted-foreground">
                                            No reviews yet. Be the first to
                                            review!
                                        </p>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Purchase Card */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="mb-6 text-center">
                                    <p className="text-4xl font-bold">
                                        {formatPrice(
                                            prompt.price,
                                            prompt.pricing_model,
                                        )}
                                    </p>
                                    {prompt.pricing_model ===
                                        'pay_what_you_want' &&
                                        prompt.min_price && (
                                            <p className="mt-1 text-sm text-muted-foreground">
                                                Minimum: $
                                                {(typeof prompt.min_price ===
                                                'number'
                                                    ? prompt.min_price
                                                    : 0
                                                ).toFixed(2)}
                                            </p>
                                        )}
                                </div>

                                {!hasPurchased && !isOwner && (
                                    <div className="space-y-2">
                                        <Button
                                            className="w-full"
                                            size="lg"
                                            onClick={handlePurchase}
                                        >
                                            <ShoppingCart className="mr-2 h-5 w-5" />
                                            Purchase Prompt
                                        </Button>
                                        <Button
                                            variant="outline"
                                            className="w-full"
                                            onClick={handleAddToCart}
                                        >
                                            Add to Cart
                                        </Button>
                                    </div>
                                )}

                                {hasPurchased && (
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-center gap-2 text-green-600">
                                            <Check className="h-5 w-5" />
                                            <span className="font-medium">
                                                Purchased
                                            </span>
                                        </div>
                                        <Button
                                            variant="outline"
                                            className="w-full"
                                            asChild
                                        >
                                            <Link href="/purchases">
                                                View in My Purchases
                                            </Link>
                                        </Button>
                                    </div>
                                )}

                                {isOwner && (
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-center gap-2 text-primary">
                                            <Check className="h-5 w-5" />
                                            <span className="font-medium">
                                                Your Prompt
                                            </span>
                                        </div>
                                        <Button
                                            variant="outline"
                                            className="w-full"
                                            asChild
                                        >
                                            <Link
                                                href={`/prompts/${prompt.id}/edit`}
                                            >
                                                Edit Prompt
                                            </Link>
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Stats Card */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="text-center">
                                        <div className="mb-1 flex items-center justify-center gap-1">
                                            <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                            <span className="text-xl font-bold">
                                                {(typeof prompt.rating ===
                                                'number'
                                                    ? prompt.rating
                                                    : 0
                                                ).toFixed(1)}
                                            </span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {prompt.rating_count} reviews
                                        </p>
                                    </div>
                                    <div className="text-center">
                                        <div className="mb-1 flex items-center justify-center gap-1">
                                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-xl font-bold">
                                                {prompt.purchases_count}
                                            </span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            purchases
                                        </p>
                                    </div>
                                    <div className="text-center">
                                        <div className="mb-1 flex items-center justify-center gap-1">
                                            <Eye className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-xl font-bold">
                                                {prompt.views_count}
                                            </span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            views
                                        </p>
                                    </div>
                                    <div className="text-center">
                                        <div className="mb-1 flex items-center justify-center gap-1">
                                            <Clock className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-xl font-bold">
                                                {prompt.version}
                                            </span>
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            version
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Seller Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    About the Seller
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="mb-4 flex items-center gap-3">
                                    <Avatar className="h-12 w-12">
                                        <AvatarImage
                                            src={
                                                prompt.seller.profile?.avatar ||
                                                undefined
                                            }
                                        />
                                        <AvatarFallback>
                                            {prompt.seller.name.charAt(0)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <p className="flex items-center gap-2 font-medium">
                                            {prompt.seller.name}
                                            {prompt.seller.profile
                                                ?.seller_verified && (
                                                <Badge
                                                    variant="outline"
                                                    className="text-xs"
                                                >
                                                    Verified
                                                </Badge>
                                            )}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {prompt.seller.profile
                                                ?.total_sales || 0}{' '}
                                            sales
                                        </p>
                                    </div>
                                </div>
                                {prompt.seller.profile?.bio && (
                                    <p className="line-clamp-3 text-sm text-muted-foreground">
                                        {prompt.seller.profile.bio}
                                    </p>
                                )}
                            </CardContent>
                        </Card>

                        {/* Details Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-sm">
                                    Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Published
                                    </span>
                                    <span>{formatDate(prompt.created_at)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Updated
                                    </span>
                                    <span>{formatDate(prompt.updated_at)}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        AI Model
                                    </span>
                                    <span>{prompt.ai_model}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Category
                                    </span>
                                    <Link
                                        href={`/marketplace?category=${prompt.category.id}`}
                                        className="hover:text-primary"
                                    >
                                        {prompt.category.name}
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
