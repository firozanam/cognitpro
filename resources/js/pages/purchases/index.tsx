import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    ShoppingBag,
    Eye,
    Star,
    Calendar,
    FileText,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    ai_model: string;
}

interface Seller {
    id: number;
    name: string;
}

interface Purchase {
    id: number;
    order_number: string;
    price: number;
    platform_fee: number;
    seller_earnings: number;
    status: string;
    payment_method: string;
    purchased_at: string;
    prompt: Prompt & { seller: Seller };
    review?: {
        id: number;
        rating: number;
    } | null;
}

interface Props {
    purchases: {
        data: Purchase[];
        current_page: number;
        last_page: number;
        total: number;
        per_page: number;
        from: number;
        to: number;
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
    };
}

export default function PurchasesIndex({ purchases }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'My Purchases',
            href: '/purchases',
        },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const getStatusBadge = (status: string) => {
        const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; color: string }> = {
            pending: { variant: 'outline', color: 'text-yellow-600' },
            completed: { variant: 'default', color: 'text-green-600' },
            refunded: { variant: 'destructive', color: 'text-red-600' },
            disputed: { variant: 'secondary', color: 'text-orange-600' },
        };

        const config = statusConfig[status] || statusConfig.pending;

        return (
            <Badge variant={config.variant} className={config.color}>
                {status.charAt(0).toUpperCase() + status.slice(1)}
            </Badge>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="My Purchases" />

            <div className="container py-8">
                {/* Header */}
                <div className="flex items-center justify-between mb-8">
                    <div>
                        <h1 className="text-3xl font-bold">My Purchases</h1>
                        <p className="text-muted-foreground mt-1">
                            View and manage your purchased prompts
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/marketplace">
                            <ShoppingBag className="h-4 w-4 mr-2" />
                            Browse Marketplace
                        </Link>
                    </Button>
                </div>

                {/* Purchases List */}
                {purchases.data.length > 0 ? (
                    <>
                        <div className="space-y-4">
                            {purchases.data.map((purchase) => (
                                <Card key={purchase.id} className="hover:shadow-md transition-shadow">
                                    <CardContent className="p-6">
                                        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                            <div className="flex-1">
                                                <div className="flex items-start gap-4">
                                                    <div className="p-3 bg-primary/10 rounded-lg">
                                                        <FileText className="h-6 w-6 text-primary" />
                                                    </div>
                                                    <div className="flex-1">
                                                        <div className="flex items-center gap-2 mb-1">
                                                            <Link
                                                                href={`/prompts/${purchase.prompt.slug}`}
                                                                className="font-semibold text-lg hover:text-primary"
                                                            >
                                                                {purchase.prompt.title}
                                                            </Link>
                                                            {getStatusBadge(purchase.status)}
                                                        </div>
                                                        <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                                            <span className="flex items-center gap-1">
                                                                <span className="font-medium">Seller:</span>
                                                                {purchase.prompt.seller.name}
                                                            </span>
                                                            <span className="flex items-center gap-1">
                                                                <Calendar className="h-4 w-4" />
                                                                {formatDate(purchase.purchased_at)}
                                                            </span>
                                                            <span className="flex items-center gap-1">
                                                                <span className="font-mono text-xs bg-muted px-2 py-0.5 rounded">
                                                                    #{purchase.order_number}
                                                                </span>
                                                            </span>
                                                        </div>
                                                        {purchase.review && (
                                                            <div className="flex items-center gap-1 mt-2">
                                                                <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                                <span className="text-sm font-medium">
                                                                    Your rating: {purchase.review.rating}/5
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="flex flex-col items-end gap-2">
                                                <div className="text-right">
                                                    <p className="text-2xl font-bold text-green-600">
                                                        {formatCurrency(purchase.price)}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        via {purchase.payment_method || 'Free'}
                                                    </p>
                                                </div>
                                                <div className="flex gap-2">
                                                    {purchase.status === 'completed' && (
                                                        <>
                                                            <Button variant="outline" size="sm" asChild>
                                                                <Link href={`/purchases/${purchase.id}`}>
                                                                    <Eye className="h-4 w-4 mr-1" />
                                                                    View
                                                                </Link>
                                                            </Button>
                                                            {!purchase.review && (
                                                                <Button size="sm" asChild>
                                                                    <Link href={`/purchases/${purchase.id}/review`}>
                                                                        <Star className="h-4 w-4 mr-1" />
                                                                        Review
                                                                    </Link>
                                                                </Button>
                                                            )}
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Pagination */}
                        {purchases.last_page > 1 && (
                            <div className="flex items-center justify-center gap-2 mt-8">
                                {purchases.links.map((link, index) => {
                                    if (link.label.includes('Previous')) {
                                        return (
                                            <Button
                                                key={index}
                                                variant="outline"
                                                size="sm"
                                                disabled={!link.url}
                                                asChild={!!link.url}
                                            >
                                                {link.url ? (
                                                    <Link href={link.url}>
                                                        <ChevronLeft className="h-4 w-4 mr-1" />
                                                        Previous
                                                    </Link>
                                                ) : (
                                                    <span>
                                                        <ChevronLeft className="h-4 w-4 mr-1" />
                                                        Previous
                                                    </span>
                                                )}
                                            </Button>
                                        );
                                    }

                                    if (link.label.includes('Next')) {
                                        return (
                                            <Button
                                                key={index}
                                                variant="outline"
                                                size="sm"
                                                disabled={!link.url}
                                                asChild={!!link.url}
                                            >
                                                {link.url ? (
                                                    <Link href={link.url}>
                                                        Next
                                                        <ChevronRight className="h-4 w-4 ml-1" />
                                                    </Link>
                                                ) : (
                                                    <span>
                                                        Next
                                                        <ChevronRight className="h-4 w-4 ml-1" />
                                                    </span>
                                                )}
                                            </Button>
                                        );
                                    }

                                    return (
                                        <Button
                                            key={index}
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            asChild={!!link.url}
                                        >
                                            {link.url ? (
                                                <Link href={link.url}>{link.label}</Link>
                                            ) : (
                                                <span>{link.label}</span>
                                            )}
                                        </Button>
                                    );
                                })}
                            </div>
                        )}

                        {/* Summary */}
                        <div className="text-center text-sm text-muted-foreground mt-4">
                            Showing {purchases.from} to {purchases.to} of {purchases.total} purchases
                        </div>
                    </>
                ) : (
                    <Card>
                        <CardContent className="py-16 text-center">
                            <ShoppingBag className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                            <h3 className="text-xl font-semibold mb-2">No purchases yet</h3>
                            <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                You haven't purchased any prompts yet. Browse the marketplace to find prompts that suit your needs.
                            </p>
                            <Button asChild>
                                <Link href="/marketplace">
                                    <ShoppingBag className="h-4 w-4 mr-2" />
                                    Browse Marketplace
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
