import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    DollarSign, 
    ShoppingBag, 
    Eye, 
    Star, 
    Plus, 
    TrendingUp,
    FileText,
    Clock,
    CheckCircle,
    XCircle,
    AlertCircle
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    status: string;
    price: number;
    views_count: number;
    purchases_count: number;
    rating: number;
    rating_count: number;
    created_at: string;
}

interface Stats {
    total_earnings: number;
    total_sales: number;
    total_prompts: number;
    total_views: number;
    average_rating: number;
    pending_payout: number;
}

interface Props {
    prompts: {
        data: Prompt[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: Stats;
}

export default function SellerDashboard({ prompts, stats }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Seller Dashboard',
            href: '/seller/dashboard',
        },
    ];

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const getStatusBadge = (status: string) => {
        const statusConfig: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; icon: React.ReactNode }> = {
            draft: { variant: 'secondary', icon: <Clock className="h-3 w-3" /> },
            pending: { variant: 'outline', icon: <AlertCircle className="h-3 w-3" /> },
            approved: { variant: 'default', icon: <CheckCircle className="h-3 w-3" /> },
            rejected: { variant: 'destructive', icon: <XCircle className="h-3 w-3" /> },
        };

        const config = statusConfig[status] || statusConfig.draft;

        return (
            <Badge variant={config.variant} className="gap-1">
                {config.icon}
                {status.charAt(0).toUpperCase() + status.slice(1)}
            </Badge>
        );
    };

    const statCards = [
        {
            title: 'Total Earnings',
            value: formatCurrency(stats.total_earnings),
            icon: DollarSign,
            description: 'Lifetime earnings',
            color: 'text-green-600',
        },
        {
            title: 'Total Sales',
            value: stats.total_sales.toString(),
            icon: ShoppingBag,
            description: 'Prompts sold',
            color: 'text-blue-600',
        },
        {
            title: 'Total Views',
            value: stats.total_views.toLocaleString(),
            icon: Eye,
            description: 'All-time views',
            color: 'text-purple-600',
        },
        {
            title: 'Average Rating',
            value: (typeof stats.average_rating === 'number' && stats.average_rating > 0) ? stats.average_rating.toFixed(1) : 'N/A',
            icon: Star,
            description: 'Based on reviews',
            color: 'text-yellow-600',
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Seller Dashboard" />
            
            <div className="container py-8">
                {/* Header */}
                <div className="flex items-center justify-between mb-8">
                    <div>
                        <h1 className="text-3xl font-bold">Seller Dashboard</h1>
                        <p className="text-muted-foreground mt-1">
                            Manage your prompts and track your earnings
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/prompts/create">
                            <Plus className="h-4 w-4 mr-2" />
                            Create Prompt
                        </Link>
                    </Button>
                </div>

                {/* Stats Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
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

                {/* Pending Payout Alert */}
                {stats.pending_payout > 0 && (
                    <Card className="mb-8 border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950">
                        <CardContent className="flex items-center justify-between py-4">
                            <div className="flex items-center gap-4">
                                <div className="p-2 bg-green-100 dark:bg-green-900 rounded-full">
                                    <TrendingUp className="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <p className="font-medium text-green-800 dark:text-green-200">
                                        Pending Payout Available
                                    </p>
                                    <p className="text-sm text-green-600 dark:text-green-400">
                                        {formatCurrency(stats.pending_payout)} ready for withdrawal
                                    </p>
                                </div>
                            </div>
                            <Button variant="outline" className="border-green-600 text-green-600 hover:bg-green-100">
                                Request Payout
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {/* Prompts Table */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <FileText className="h-5 w-5" />
                            Your Prompts
                        </CardTitle>
                        <CardDescription>
                            You have {prompts.total} prompts in total
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {prompts.data.length > 0 ? (
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
                                            <th className="text-right py-3 px-4 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {prompts.data.map((prompt) => (
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
                                                    ${(typeof prompt.price === 'number' ? prompt.price : 0).toFixed(2)}
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
                                                            {(typeof prompt.rating === 'number' ? prompt.rating : 0).toFixed(1)}
                                                        </div>
                                                    ) : (
                                                        <span className="text-muted-foreground">-</span>
                                                    )}
                                                </td>
                                                <td className="py-3 px-4 text-right">
                                                    <Button variant="ghost" size="sm" asChild>
                                                        <Link href={`/prompts/${prompt.id}/edit`}>
                                                            Edit
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                                <h3 className="text-lg font-semibold mb-2">No prompts yet</h3>
                                <p className="text-muted-foreground mb-4">
                                    Create your first prompt to start selling
                                </p>
                                <Button asChild>
                                    <Link href="/prompts/create">
                                        <Plus className="h-4 w-4 mr-2" />
                                        Create Your First Prompt
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
