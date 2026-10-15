import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency, formatDate } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import {
    type Category,
    type Prompt,
    type Seller,
    type Tag as TagType,
} from '@/types/prompt';
import { Head, Link } from '@inertiajs/react';
import {
    Calendar,
    CheckCircle,
    Copy,
    Eye,
    FileText,
    MessageSquare,
    Sparkles,
    Star,
    Tag,
} from 'lucide-react';
import { useState } from 'react';

interface Review {
    id: number;
    rating: number;
    title: string;
    content: string;
}

type PurchasePrompt = Pick<
    Prompt,
    'id' | 'title' | 'slug' | 'description' | 'content' | 'ai_model'
> & {
    category: Category;
    tags: TagType[];
    seller: Seller;
};

interface Purchase {
    id: number;
    order_number: string;
    price: number;
    platform_fee: number;
    seller_earnings: number;
    status: string;
    payment_method: string;
    purchased_at: string | null;
    prompt: PurchasePrompt;
    review?: Review | null;
}

interface Props {
    purchase: Purchase;
    can_review: boolean;
}

export default function PurchaseShow({ purchase, can_review }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'My Purchases',
            href: '/purchases',
        },
        {
            title: `Order #${purchase.order_number}`,
            href: `/purchases/${purchase.id}`,
        },
    ];

    const [copied, setCopied] = useState(false);

    const handleCopyContent = () => {
        navigator.clipboard.writeText(purchase.prompt.content ?? '');
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const getStatusBadge = (status: string) => {
        const statusConfig: Record<
            string,
            {
                variant: 'default' | 'secondary' | 'destructive' | 'outline';
                color: string;
            }
        > = {
            pending: { variant: 'outline', color: 'text-yellow-600' },
            completed: { variant: 'default', color: '' },
            refunded: { variant: 'destructive', color: '' },
            disputed: { variant: 'secondary', color: '' },
        };

        const config = statusConfig[status] || statusConfig.pending;

        return (
            <Badge variant={config.variant} className={config.color}>
                {status.charAt(0).toUpperCase() + status.slice(1)}
            </Badge>
        );
    };

    const getInitials = (name: string) => {
        return name
            .split(' ')
            .map((n) => n[0])
            .join('')
            .toUpperCase()
            .substring(0, 2);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order #${purchase.order_number}`} />

            <div className="container max-w-4xl py-8">
                {/* Header */}
                <div className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <div className="mb-1 flex items-center gap-2">
                            <h1 className="text-2xl font-bold">
                                Order #{purchase.order_number}
                            </h1>
                            {getStatusBadge(purchase.status)}
                        </div>
                        <p className="flex items-center gap-2 text-muted-foreground">
                            <Calendar className="h-4 w-4" />
                            Purchased on{' '}
                            {formatDate(purchase.purchased_at, {
                                withTime: true,
                            })}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/purchases">
                                <Eye className="mr-2 h-4 w-4" />
                                View All Purchases
                            </Link>
                        </Button>
                        {purchase.status === 'completed' && (
                            <Button asChild>
                                <Link href={`/marketplace`}>
                                    <Sparkles className="mr-2 h-4 w-4" />
                                    Browse More
                                </Link>
                            </Button>
                        )}
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    {/* Main Content */}
                    <div className="space-y-6 md:col-span-2">
                        {/* Prompt Content */}
                        {purchase.status === 'completed' && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center justify-between">
                                        <span className="flex items-center gap-2">
                                            <FileText className="h-5 w-5" />
                                            Prompt Content
                                        </span>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={handleCopyContent}
                                        >
                                            {copied ? (
                                                <>
                                                    <CheckCircle className="mr-2 h-4 w-4" />
                                                    Copied!
                                                </>
                                            ) : (
                                                <>
                                                    <Copy className="mr-2 h-4 w-4" />
                                                    Copy
                                                </>
                                            )}
                                        </Button>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="rounded-lg bg-muted p-4 font-mono text-sm whitespace-pre-wrap">
                                        {purchase.prompt.content}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Prompt Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Prompt Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <Label className="text-muted-foreground">
                                        Title
                                    </Label>
                                    <p className="font-medium">
                                        {purchase.prompt.title}
                                    </p>
                                </div>
                                <div>
                                    <Label className="text-muted-foreground">
                                        Description
                                    </Label>
                                    <p className="text-sm">
                                        {purchase.prompt.description}
                                    </p>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    <Badge variant="secondary">
                                        {purchase.prompt.ai_model}
                                    </Badge>
                                    <Badge variant="outline">
                                        {purchase.prompt.category.name}
                                    </Badge>
                                    {purchase.prompt.tags.map((tag) => (
                                        <Badge
                                            key={tag.id}
                                            variant="outline"
                                            className="text-xs"
                                        >
                                            <Tag className="mr-1 h-3 w-3" />
                                            {tag.name}
                                        </Badge>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Review Section */}
                        {purchase.review ? (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MessageSquare className="h-5 w-5" />
                                        Your Review
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-2">
                                            {[1, 2, 3, 4, 5].map((star) => (
                                                <Star
                                                    key={star}
                                                    className={`h-5 w-5 ${
                                                        star <=
                                                        purchase.review!.rating
                                                            ? 'fill-yellow-400 text-yellow-400'
                                                            : 'text-muted-foreground'
                                                    }`}
                                                />
                                            ))}
                                        </div>
                                        {purchase.review.title && (
                                            <p className="font-medium">
                                                {purchase.review.title}
                                            </p>
                                        )}
                                        {purchase.review.content && (
                                            <p className="text-sm text-muted-foreground">
                                                {purchase.review.content}
                                            </p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ) : can_review && purchase.status === 'completed' ? (
                            <Card className="border-dashed">
                                <CardContent className="py-8 text-center">
                                    <Star className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                                    <h3 className="mb-2 font-semibold">
                                        Share Your Experience
                                    </h3>
                                    <p className="mb-4 text-sm text-muted-foreground">
                                        Help others by leaving a review for this
                                        prompt
                                    </p>
                                    <Button asChild>
                                        <Link
                                            href={`/purchases/${purchase.id}/review`}
                                        >
                                            <Star className="mr-2 h-4 w-4" />
                                            Write a Review
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        ) : null}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Order Summary */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Order Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Price
                                    </span>
                                    <span>
                                        {formatCurrency(purchase.price)}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">
                                        Platform fee
                                    </span>
                                    <span className="text-muted-foreground">
                                        {formatCurrency(purchase.platform_fee)}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span className="text-green-600">
                                        {formatCurrency(purchase.price)}
                                    </span>
                                </div>
                                <Separator />
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">
                                        Payment Method
                                    </span>
                                    <span className="capitalize">
                                        {purchase.payment_method || 'Free'}
                                    </span>
                                </div>
                                <div className="flex justify-between text-sm">
                                    <span className="text-muted-foreground">
                                        Status
                                    </span>
                                    {getStatusBadge(purchase.status)}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Seller Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Seller</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="flex items-center gap-3">
                                    <Avatar>
                                        <AvatarImage
                                            src={
                                                purchase.prompt.seller.profile
                                                    ?.avatar || ''
                                            }
                                            alt={purchase.prompt.seller.name}
                                        />
                                        <AvatarFallback>
                                            {getInitials(
                                                purchase.prompt.seller.name,
                                            )}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <p className="font-medium">
                                            {purchase.prompt.seller.name}
                                        </p>
                                        {purchase.prompt.seller.profile
                                            ?.seller_verified && (
                                            <Badge
                                                variant="secondary"
                                                className="text-xs"
                                            >
                                                <CheckCircle className="mr-1 h-3 w-3" />
                                                Verified
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Help */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">
                                    Need Help?
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button
                                    variant="outline"
                                    className="w-full justify-start"
                                    asChild
                                >
                                    <Link href="/support">
                                        <MessageSquare className="mr-2 h-4 w-4" />
                                        Contact Support
                                    </Link>
                                </Button>
                                <Button
                                    variant="outline"
                                    className="w-full justify-start"
                                    asChild
                                >
                                    <Link href="/faq">
                                        <FileText className="mr-2 h-4 w-4" />
                                        FAQ
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function Label({
    className,
    children,
}: {
    className?: string;
    children: React.ReactNode;
}) {
    return (
        <p className={`text-xs tracking-wide uppercase ${className}`}>
            {children}
        </p>
    );
}
