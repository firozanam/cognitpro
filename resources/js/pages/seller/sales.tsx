import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DollarSign,
    ShoppingBag,
    TrendingUp,
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
}

interface Buyer {
    id: number;
    name: string;
    profile?: {
        avatar: string | null;
    };
}

interface Sale {
    id: number;
    order_number: string;
    price: number;
    platform_fee: number;
    seller_earnings: number;
    status: string;
    payment_method: string;
    purchased_at: string;
    prompt: Prompt;
    buyer: Buyer;
}

interface Stats {
    total_sales: number;
    total_earnings: number;
    average_order_value: number;
}

interface Props {
    sales: {
        data: Sale[];
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
    stats: Stats;
}

export default function SellerSales({ sales, stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Seller Dashboard',
            href: '/seller/dashboard',
        },
        {
            title: 'Sales',
            href: '/seller/sales',
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
            month: 'short',
            day: 'numeric',
        });
    };

    const getStatusBadge = (status: string) => {
        const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; color: string }> = {
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

    const statCards = [
        {
            title: 'Total Sales',
            value: stats.total_sales.toString(),
            icon: ShoppingBag,
            description: 'All-time sales',
            color: 'text-blue-600',
        },
        {
            title: 'Total Earnings',
            value: formatCurrency(stats.total_earnings),
            icon: DollarSign,
            description: 'After platform fees',
            color: 'text-green-600',
        },
        {
            title: 'Average Order',
            value: formatCurrency(stats.average_order_value),
            icon: TrendingUp,
            description: 'Per sale average',
            color: 'text-purple-600',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sales - Seller Dashboard" />

            <div className="container py-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                    <div>
                        <h1 className="text-3xl font-bold">Sales</h1>
                        <p className="text-muted-foreground mt-1">
                            Track your prompt sales and earnings
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/seller/dashboard">
                            <FileText className="h-4 w-4 mr-2" />
                            Manage Prompts
                        </Link>
                    </Button>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-3 mb-8">
                    {statCards.map((stat) => (
                        <Card key={stat.title}>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    {stat.title}
                                </CardTitle>
                                <stat.icon className={`h-4 w-4 ${stat.color}`} />
                            </CardHeader>
                            <CardContent>
                                <div className={`text-2xl font-bold ${stat.color}`}>
                                    {stat.value}
                                </div>
                                <p className="text-xs text-muted-foreground">
                                    {stat.description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {/* Sales Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>Sales History</CardTitle>
                        <CardDescription>
                            {sales.total} sales in total
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {sales.data.length > 0 ? (
                            <>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left py-3 px-4 font-medium">Order</th>
                                                <th className="text-left py-3 px-4 font-medium">Prompt</th>
                                                <th className="text-left py-3 px-4 font-medium">Buyer</th>
                                                <th className="text-left py-3 px-4 font-medium">Date</th>
                                                <th className="text-left py-3 px-4 font-medium">Amount</th>
                                                <th className="text-left py-3 px-4 font-medium">Earnings</th>
                                                <th className="text-left py-3 px-4 font-medium">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {sales.data.map((sale) => (
                                                <tr key={sale.id} className="border-b hover:bg-muted/50">
                                                    <td className="py-3 px-4">
                                                        <span className="font-mono text-sm">
                                                            #{sale.order_number}
                                                        </span>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <Link
                                                            href={`/prompts/${sale.prompt.slug}`}
                                                            className="font-medium hover:text-primary line-clamp-1"
                                                        >
                                                            {sale.prompt.title}
                                                        </Link>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center gap-2">
                                                            <Avatar className="h-6 w-6">
                                                                <AvatarImage
                                                                    src={sale.buyer.profile?.avatar || ''}
                                                                    alt={sale.buyer.name}
                                                                />
                                                                <AvatarFallback className="text-xs">
                                                                    {getInitials(sale.buyer.name)}
                                                                </AvatarFallback>
                                                            </Avatar>
                                                            <span className="text-sm">
                                                                {sale.buyer.name}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                                            <Calendar className="h-4 w-4" />
                                                            {formatDate(sale.purchased_at)}
                                                        </div>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <span className="font-medium">
                                                            {formatCurrency(sale.price)}
                                                        </span>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        <span className="font-medium text-green-600">
                                                            {formatCurrency(sale.seller_earnings)}
                                                        </span>
                                                        <p className="text-xs text-muted-foreground">
                                                            -{formatCurrency(sale.platform_fee)} fee
                                                        </p>
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {getStatusBadge(sale.status)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Pagination */}
                                {sales.last_page > 1 && (
                                    <div className="flex items-center justify-center gap-2 mt-6">
                                        {sales.links.map((link, index) => {
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
                                    Showing {sales.from} to {sales.to} of {sales.total} sales
                                </div>
                            </>
                        ) : (
                            <div className="text-center py-12">
                                <ShoppingBag className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">No sales yet</h3>
                                <p className="text-muted-foreground mb-4">
                                    Your sales will appear here once you start selling prompts
                                </p>
                                <Button asChild>
                                    <Link href="/seller/dashboard">
                                        <FileText className="h-4 w-4 mr-2" />
                                        Manage Your Prompts
                                    </Link>
                                </Button>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
