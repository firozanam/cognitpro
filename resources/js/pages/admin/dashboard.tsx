import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency, formatDate } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { type Prompt } from '@/types/prompt';
import { Head, Link, router } from '@inertiajs/react';
import {
    AlertCircle,
    ArrowRight,
    CheckCircle,
    DollarSign,
    FileText,
    ShoppingCart,
    TrendingUp,
    Users,
} from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Purchase {
    id: number;
    order_number: string;
    price: number;
    buyer: {
        name: string;
    };
    created_at: string;
}

interface Stats {
    total_users: number;
    total_sellers: number;
    total_prompts: number;
    pending_prompts: number;
    total_sales: number;
    total_revenue: number;
}

type AdminPrompt = Pick<Prompt, 'id' | 'title' | 'status' | 'created_at'> & {
    status: string;
    seller: { name: string };
};

interface Props {
    stats: Stats;
    recentUsers: User[];
    recentPurchases: Purchase[];
    pendingPrompts: AdminPrompt[];
}

export default function AdminDashboard({
    stats,
    recentUsers,
    recentPurchases,
    pendingPrompts,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin Dashboard',
            href: '/admin',
        },
    ];

    const statCards = [
        {
            title: 'Total Users',
            value: stats.total_users.toLocaleString(),
            icon: Users,
            description: `${stats.total_sellers} sellers`,
            color: 'text-info',
            href: '/admin/users',
        },
        {
            title: 'Total Prompts',
            value: stats.total_prompts.toLocaleString(),
            icon: FileText,
            description: `${stats.pending_prompts} pending review`,
            color: 'text-chart-5',
            href: '/admin/prompts',
        },
        {
            title: 'Total Sales',
            value: stats.total_sales.toLocaleString(),
            icon: ShoppingCart,
            description: 'Completed orders',
            color: 'text-success',
            href: null,
        },
        {
            title: 'Total Revenue',
            value: formatCurrency(stats.total_revenue),
            icon: DollarSign,
            description: 'Platform earnings',
            color: 'text-warning',
            href: null,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Admin Dashboard
                        </h1>
                        <p className="text-muted-foreground">
                            Manage your platform and monitor performance
                        </p>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-4">
                    {statCards.map((stat) => (
                        <Card
                            key={stat.title}
                            className={
                                stat.href
                                    ? 'cursor-pointer transition-shadow hover:shadow-md'
                                    : ''
                            }
                        >
                            {stat.href ? (
                                <Link href={stat.href}>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            {stat.title}
                                        </CardTitle>
                                        <stat.icon
                                            className={`h-4 w-4 ${stat.color}`}
                                        />
                                    </CardHeader>
                                    <CardContent>
                                        <div
                                            className={`text-2xl font-bold ${stat.color}`}
                                        >
                                            {stat.value}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {stat.description}
                                        </p>
                                    </CardContent>
                                </Link>
                            ) : (
                                <>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            {stat.title}
                                        </CardTitle>
                                        <stat.icon
                                            className={`h-4 w-4 ${stat.color}`}
                                        />
                                    </CardHeader>
                                    <CardContent>
                                        <div
                                            className={`text-2xl font-bold ${stat.color}`}
                                        >
                                            {stat.value}
                                        </div>
                                        <p className="text-xs text-muted-foreground">
                                            {stat.description}
                                        </p>
                                    </CardContent>
                                </>
                            )}
                        </Card>
                    ))}
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {/* Pending Prompts */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <AlertCircle className="h-5 w-5 text-warning" />
                                        Pending Prompts
                                    </CardTitle>
                                    <CardDescription>
                                        Prompts awaiting review
                                    </CardDescription>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/prompts?status=pending">
                                        View All
                                        <ArrowRight className="ml-1 h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {pendingPrompts.length > 0 ? (
                                <div className="space-y-4">
                                    {pendingPrompts.map((prompt) => (
                                        <div
                                            key={prompt.id}
                                            className="flex items-center justify-between rounded-lg bg-muted p-3"
                                        >
                                            <div>
                                                <p className="line-clamp-1 font-medium">
                                                    {prompt.title}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    by {prompt.seller.name} •{' '}
                                                    {formatDate(
                                                        prompt.created_at,
                                                    )}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    title="Approve"
                                                    onClick={() =>
                                                        router.post(
                                                            `/admin/prompts/${prompt.id}/approve`,
                                                        )
                                                    }
                                                >
                                                    <CheckCircle className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    <CheckCircle className="mx-auto mb-2 h-12 w-12 opacity-50" />
                                    <p>No pending prompts</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Recent Users */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <Users className="h-5 w-5 text-info" />
                                        Recent Users
                                    </CardTitle>
                                    <CardDescription>
                                        Newest registered users
                                    </CardDescription>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/users">
                                        View All
                                        <ArrowRight className="ml-1 h-4 w-4" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {recentUsers.length > 0 ? (
                                <div className="space-y-4">
                                    {recentUsers.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex items-center justify-between rounded-lg bg-muted p-3"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {user.name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {user.email}
                                                </p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Badge
                                                    variant={
                                                        user.role === 'admin'
                                                            ? 'default'
                                                            : 'secondary'
                                                    }
                                                >
                                                    {user.role}
                                                </Badge>
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDate(
                                                        user.created_at,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    <Users className="mx-auto mb-2 h-12 w-12 opacity-50" />
                                    <p>No users yet</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Recent Sales */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5 text-success" />
                                Recent Sales
                            </CardTitle>
                            <CardDescription>
                                Latest completed purchases
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentPurchases.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="px-4 py-3 text-left font-medium">
                                                    Order
                                                </th>
                                                <th className="px-4 py-3 text-left font-medium">
                                                    Buyer
                                                </th>
                                                <th className="px-4 py-3 text-left font-medium">
                                                    Amount
                                                </th>
                                                <th className="px-4 py-3 text-left font-medium">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {recentPurchases.map((purchase) => (
                                                <tr
                                                    key={purchase.id}
                                                    className="border-b"
                                                >
                                                    <td className="px-4 py-3 font-mono text-sm">
                                                        #{purchase.order_number}
                                                    </td>
                                                    <td className="px-4 py-3">
                                                        {purchase.buyer.name}
                                                    </td>
                                                    <td className="px-4 py-3 font-medium text-success">
                                                        {formatCurrency(
                                                            purchase.price,
                                                        )}
                                                    </td>
                                                    <td className="px-4 py-3 text-muted-foreground">
                                                        {formatDate(
                                                            purchase.created_at,
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="py-8 text-center text-muted-foreground">
                                    <ShoppingCart className="mx-auto mb-2 h-12 w-12 opacity-50" />
                                    <p>No sales yet</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
