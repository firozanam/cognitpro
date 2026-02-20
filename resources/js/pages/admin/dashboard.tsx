import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Users,
    FileText,
    DollarSign,
    ShoppingCart,
    AlertCircle,
    CheckCircle,
    TrendingUp,
    ArrowRight,
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    created_at: string;
}

interface Prompt {
    id: number;
    title: string;
    status: string;
    seller: {
        name: string;
    };
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

interface Props {
    stats: Stats;
    recentUsers: User[];
    recentPurchases: Purchase[];
    pendingPrompts: Prompt[];
}

export default function AdminDashboard({ stats, recentUsers, recentPurchases, pendingPrompts }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin Dashboard',
            href: '/admin',
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

    const statCards = [
        {
            title: 'Total Users',
            value: stats.total_users.toLocaleString(),
            icon: Users,
            description: `${stats.total_sellers} sellers`,
            color: 'text-blue-600',
            href: '/admin/users',
        },
        {
            title: 'Total Prompts',
            value: stats.total_prompts.toLocaleString(),
            icon: FileText,
            description: `${stats.pending_prompts} pending review`,
            color: 'text-purple-600',
            href: '/admin/prompts',
        },
        {
            title: 'Total Sales',
            value: stats.total_sales.toLocaleString(),
            icon: ShoppingCart,
            description: 'Completed orders',
            color: 'text-green-600',
            href: null,
        },
        {
            title: 'Total Revenue',
            value: formatCurrency(stats.total_revenue),
            icon: DollarSign,
            description: 'Platform earnings',
            color: 'text-yellow-600',
            href: null,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />

            <div className="container py-8">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold">Admin Dashboard</h1>
                    <p className="text-muted-foreground mt-1">
                        Manage your platform and monitor performance
                    </p>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
                    {statCards.map((stat) => (
                        <Card key={stat.title} className={stat.href ? 'cursor-pointer hover:shadow-md transition-shadow' : ''}>
                            {stat.href ? (
                                <Link href={stat.href}>
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
                                </Link>
                            ) : (
                                <>
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
                                </>
                            )}
                        </Card>
                    ))}
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Pending Prompts */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <div>
                                    <CardTitle className="flex items-center gap-2">
                                        <AlertCircle className="h-5 w-5 text-yellow-500" />
                                        Pending Prompts
                                    </CardTitle>
                                    <CardDescription>Prompts awaiting review</CardDescription>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/prompts?status=pending">
                                        View All
                                        <ArrowRight className="h-4 w-4 ml-1" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {pendingPrompts.length > 0 ? (
                                <div className="space-y-4">
                                    {pendingPrompts.map((prompt) => (
                                        <div key={prompt.id} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                                            <div>
                                                <p className="font-medium line-clamp-1">{prompt.title}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    by {prompt.seller.name} • {formatDate(prompt.created_at)}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Button size="sm" variant="outline" asChild>
                                                    <Link href={`/admin/prompts/${prompt.id}/approve`}>
                                                        <CheckCircle className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    <CheckCircle className="h-12 w-12 mx-auto mb-2 opacity-50" />
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
                                        <Users className="h-5 w-5 text-blue-500" />
                                        Recent Users
                                    </CardTitle>
                                    <CardDescription>Newest registered users</CardDescription>
                                </div>
                                <Button variant="outline" size="sm" asChild>
                                    <Link href="/admin/users">
                                        View All
                                        <ArrowRight className="h-4 w-4 ml-1" />
                                    </Link>
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {recentUsers.length > 0 ? (
                                <div className="space-y-4">
                                    {recentUsers.map((user) => (
                                        <div key={user.id} className="flex items-center justify-between p-3 bg-muted rounded-lg">
                                            <div>
                                                <p className="font-medium">{user.name}</p>
                                                <p className="text-sm text-muted-foreground">{user.email}</p>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Badge variant={user.role === 'admin' ? 'default' : 'secondary'}>
                                                    {user.role}
                                                </Badge>
                                                <span className="text-xs text-muted-foreground">
                                                    {formatDate(user.created_at)}
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    <Users className="h-12 w-12 mx-auto mb-2 opacity-50" />
                                    <p>No users yet</p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Recent Sales */}
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <TrendingUp className="h-5 w-5 text-green-500" />
                                Recent Sales
                            </CardTitle>
                            <CardDescription>Latest completed purchases</CardDescription>
                        </CardHeader>
                        <CardContent>
                            {recentPurchases.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="text-left py-3 px-4 font-medium">Order</th>
                                                <th className="text-left py-3 px-4 font-medium">Buyer</th>
                                                <th className="text-left py-3 px-4 font-medium">Amount</th>
                                                <th className="text-left py-3 px-4 font-medium">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {recentPurchases.map((purchase) => (
                                                <tr key={purchase.id} className="border-b">
                                                    <td className="py-3 px-4 font-mono text-sm">
                                                        #{purchase.order_number}
                                                    </td>
                                                    <td className="py-3 px-4">
                                                        {purchase.buyer.name}
                                                    </td>
                                                    <td className="py-3 px-4 font-medium text-green-600">
                                                        {formatCurrency(purchase.price)}
                                                    </td>
                                                    <td className="py-3 px-4 text-muted-foreground">
                                                        {formatDate(purchase.created_at)}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8 text-muted-foreground">
                                    <ShoppingCart className="h-12 w-12 mx-auto mb-2 opacity-50" />
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
