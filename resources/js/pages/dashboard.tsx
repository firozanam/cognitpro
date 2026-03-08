import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    ShoppingBag,
    DollarSign,
    Eye,
    Star,
    Plus,
    FileText,
    TrendingUp,
    ShoppingCart,
    Package,
    ArrowRight,
    Sparkles,
} from 'lucide-react';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    ai_model: string;
    seller: {
        name: string;
    };
}

interface Purchase {
    id: number;
    order_number: string;
    price: number;
    status: string;
    purchased_at: string;
    prompt: Prompt;
}

interface Sale {
    id: number;
    order_number: string;
    price: number;
    seller_earnings: number;
    status: string;
    purchased_at: string;
    prompt: {
        id: number;
        title: string;
        slug: string;
    };
    buyer: {
        id: number;
        name: string;
    };
}

interface UserPrompt {
    id: number;
    title: string;
    slug: string;
    status: string;
    price: number;
    views_count: number;
    purchases_count: number;
    rating: number;
    rating_count: number;
}

interface Stats {
    total_purchases: number;
    total_spent: number;
    total_sales: number;
    total_earnings: number;
    total_prompts: number;
    total_views: number;
    cart_items: number;
}

interface Props {
    stats: Stats;
    recentPurchases: Purchase[];
    recentSales: Sale[];
    prompts: UserPrompt[];
}

export default function Dashboard({ stats, recentPurchases, recentSales, prompts }: Props) {
    const { auth } = usePage<SharedData>().props;
    const user = auth?.user;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
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
            month: 'short',
            day: 'numeric',
        });
    };

    const getStatusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
            completed: 'default',
            pending: 'outline',
            draft: 'secondary',
            approved: 'default',
            rejected: 'destructive',
        };
        return (
            <Badge variant={variants[status] || 'secondary'}>
                {status.charAt(0).toUpperCase() + status.slice(1)}
            </Badge>
        );
    };

    const isSeller = user?.role === 'seller' || user?.role === 'admin';
    const isBuyer = user?.role === 'buyer' || user?.role === 'seller' || user?.role === 'admin';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Welcome back, {user?.name?.split(' ')[0] || 'User'}!
                        </h1>
                        <p className="text-muted-foreground">
                            Here's what's happening with your account
                        </p>
                    </div>
                    {isSeller && (
                        <Button asChild>
                            <Link href="/prompts/create">
                                <Plus className="h-4 w-4 mr-2" />
                                Create Prompt
                            </Link>
                        </Button>
                    )}
                </div>

                {/* Stats Grid - Buyer Stats */}
                {isBuyer && (
                    <div className="grid gap-4 md:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Purchases</CardTitle>
                                <Package className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">
                                    {stats.total_purchases}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    Prompts bought
                                </p>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
                                <DollarSign className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">
                                    {formatCurrency(stats.total_spent)}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    On prompts
                                </p>
                            </CardContent>
                        </Card>
                        <Link href="/cart" className="block">
                            <Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Cart Items</CardTitle>
                                    <ShoppingCart className="h-4 w-4 text-purple-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-purple-600">
                                        {stats.cart_items}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Ready to checkout
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/marketplace" className="block">
                            <Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Explore</CardTitle>
                                    <Sparkles className="h-4 w-4 text-yellow-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-yellow-600">
                                        Browse
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Find new prompts
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                    </div>
                )}

                {/* Stats Grid - Seller Stats */}
                {isSeller && (
                    <div className="grid gap-4 md:grid-cols-4">
                        <Link href="/seller/sales" className="block">
                            <Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total Sales</CardTitle>
                                    <ShoppingBag className="h-4 w-4 text-blue-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-blue-600">
                                        {stats.total_sales}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Prompts sold
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/seller/sales" className="block">
                            <Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">Total Earnings</CardTitle>
                                    <DollarSign className="h-4 w-4 text-green-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-green-600">
                                        {formatCurrency(stats.total_earnings)}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        After fees
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                        <Link href="/seller/dashboard" className="block">
                            <Card className="cursor-pointer hover:shadow-md transition-shadow h-full">
                                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                    <CardTitle className="text-sm font-medium">My Prompts</CardTitle>
                                    <FileText className="h-4 w-4 text-purple-600" />
                                </CardHeader>
                                <CardContent>
                                    <div className="text-2xl font-bold text-purple-600">
                                        {stats.total_prompts}
                                    </div>
                                    <p className="text-xs text-muted-foreground">
                                        Created prompts
                                    </p>
                                </CardContent>
                            </Card>
                        </Link>
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Views</CardTitle>
                                <Eye className="h-4 w-4 text-orange-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-orange-600">
                                    {stats.total_views.toLocaleString()}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    All-time views
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                )}

                {/* Content Grid */}
                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Recent Purchases (for buyers) */}
                    {isBuyer && (
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2">
                                            <Package className="h-5 w-5 text-blue-500" />
                                            Recent Purchases
                                        </CardTitle>
                                        <CardDescription>Your latest bought prompts</CardDescription>
                                    </div>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href="/purchases">
                                            View All
                                            <ArrowRight className="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recentPurchases.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentPurchases.map((purchase) => (
                                            <div key={purchase.id} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                                                <div className="flex-1 min-w-0">
                                                    <Link
                                                        href={`/prompts/${purchase.prompt.slug}`}
                                                        className="font-medium hover:text-primary line-clamp-1"
                                                    >
                                                        {purchase.prompt.title}
                                                    </Link>
                                                    <p className="text-sm text-muted-foreground">
                                                        by {purchase.prompt.seller.name} • {formatDate(purchase.purchased_at)}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-semibold text-green-600">
                                                        {formatCurrency(purchase.price)}
                                                    </span>
                                                    {getStatusBadge(purchase.status)}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <Package className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>No purchases yet</p>
                                        <Button variant="outline" size="sm" className="mt-4" asChild>
                                            <Link href="/marketplace">Browse Marketplace</Link>
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* Recent Sales (for sellers) */}
                    {isSeller && (
                        <Card>
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2">
                                            <TrendingUp className="h-5 w-5 text-green-500" />
                                            Recent Sales
                                        </CardTitle>
                                        <CardDescription>Your latest prompt sales</CardDescription>
                                    </div>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href="/seller/sales">
                                            View All
                                            <ArrowRight className="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {recentSales.length > 0 ? (
                                    <div className="space-y-4">
                                        {recentSales.map((sale) => (
                                            <div key={sale.id} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                                                <div className="flex-1 min-w-0">
                                                    <Link
                                                        href={`/prompts/${sale.prompt.slug}`}
                                                        className="font-medium hover:text-primary line-clamp-1"
                                                    >
                                                        {sale.prompt.title}
                                                    </Link>
                                                    <p className="text-sm text-muted-foreground">
                                                        by {sale.buyer.name} • {formatDate(sale.purchased_at)}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-semibold text-green-600">
                                                        +{formatCurrency(sale.seller_earnings)}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <TrendingUp className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>No sales yet</p>
                                        <Button variant="outline" size="sm" className="mt-4" asChild>
                                            <Link href="/prompts/create">Create Your First Prompt</Link>
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* My Prompts (for sellers) */}
                    {isSeller && (
                        <Card className="lg:col-span-2">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <div>
                                        <CardTitle className="flex items-center gap-2">
                                            <FileText className="h-5 w-5 text-purple-500" />
                                            My Prompts
                                        </CardTitle>
                                        <CardDescription>Manage your created prompts</CardDescription>
                                    </div>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link href="/seller/dashboard">
                                            View All
                                            <ArrowRight className="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent>
                                {prompts.length > 0 ? (
                                    <div className="overflow-x-auto">
                                        <table className="w-full">
                                            <thead>
                                                <tr className="border-b">
                                                    <th className="text-left py-3 px-4 font-medium">Title</th>
                                                    <th className="text-left py-3 px-4 font-medium">Status</th>
                                                    <th className="text-left py-3 px-4 font-medium">Price</th>
                                                    <th className="text-left py-3 px-4 font-medium">Views</th>
                                                    <th className="text-left py-3 px-4 font-medium">Sales</th>
                                                    <th className="text-left py-3 px-4 font-medium">Rating</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {prompts.map((prompt) => (
                                                    <tr key={prompt.id} className="border-b hover:bg-muted/50">
                                                        <td className="py-3 px-4">
                                                            <Link
                                                                href={`/prompts/${prompt.slug}`}
                                                                className="font-medium hover:text-primary"
                                                            >
                                                                {prompt.title}
                                                            </Link>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            {getStatusBadge(prompt.status)}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            ${prompt.price.toFixed(2)}
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <div className="flex items-center gap-1">
                                                                <Eye className="h-4 w-4 text-muted-foreground" />
                                                                {prompt.views_count}
                                                            </div>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            <div className="flex items-center gap-1">
                                                                <ShoppingBag className="h-4 w-4 text-muted-foreground" />
                                                                {prompt.purchases_count}
                                                            </div>
                                                        </td>
                                                        <td className="py-3 px-4">
                                                            {prompt.rating_count > 0 ? (
                                                                <div className="flex items-center gap-1">
                                                                    <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                                    {prompt.rating.toFixed(1)}
                                                                </div>
                                                            ) : (
                                                                <span className="text-muted-foreground">-</span>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                ) : (
                                    <div className="text-center py-8 text-muted-foreground">
                                        <FileText className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                        <p>No prompts yet</p>
                                        <Button className="mt-4" asChild>
                                            <Link href="/prompts/create">
                                                <Plus className="h-4 w-4 mr-2" />
                                                Create Your First Prompt
                                            </Link>
                                        </Button>
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    )}

                    {/* Quick Actions (for buyers without seller content) */}
                    {!isSeller && isBuyer && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Sparkles className="h-5 w-5 text-yellow-500" />
                                    Quick Actions
                                </CardTitle>
                                <CardDescription>Get started with the marketplace</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <Button variant="outline" className="h-auto py-4 justify-start" asChild>
                                        <Link href="/marketplace">
                                            <div className="flex items-center gap-3">
                                                <div className="p-2 bg-primary/10 rounded-lg">
                                                    <Sparkles className="h-5 w-5 text-primary" />
                                                </div>
                                                <div className="text-left">
                                                    <p className="font-medium">Browse Marketplace</p>
                                                    <p className="text-xs text-muted-foreground">Find prompts for your needs</p>
                                                </div>
                                            </div>
                                        </Link>
                                    </Button>
                                    <Button variant="outline" className="h-auto py-4 justify-start" asChild>
                                        <Link href="/cart">
                                            <div className="flex items-center gap-3">
                                                <div className="p-2 bg-primary/10 rounded-lg">
                                                    <ShoppingCart className="h-5 w-5 text-primary" />
                                                </div>
                                                <div className="text-left">
                                                    <p className="font-medium">View Cart</p>
                                                    <p className="text-xs text-muted-foreground">{stats.cart_items} items waiting</p>
                                                </div>
                                            </div>
                                        </Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
