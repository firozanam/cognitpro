import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import ErrorBoundary from '@/components/error-boundary';
import {
  BarChart3,
  TrendingUp,
  DollarSign,
  Eye,
  Calendar,
  AlertCircle
} from 'lucide-react';
import { safeToFixed } from '@/lib/utils';

interface AnalyticsProps {
  monthlyStats?: Array<{
    month: number;
    year: number;
    sales: number;
    revenue: number;
  }> | null;
  topPrompts?: Array<{
    id: number;
    uuid: string;
    title: string;
    purchases_count: number;
    total_revenue: number;
    average_rating: number;
    status: string;
    created_at: string;
  }> | null;
  sellerStats?: {
    total_prompts: number;
    published_prompts: number;
    draft_prompts: number;
    total_sales: number;
    total_revenue: number;
    average_rating: number;
    total_reviews: number;
    this_month_sales: number;
    this_month_revenue: number;
  } | null;
  revenueTrends?: Array<{
    date: string;
    sales: number;
    revenue: number;
  }> | null;
}

// Loading skeleton component for analytics
function AnalyticsLoadingSkeleton() {
  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <Skeleton className="h-8 w-48 mb-2" />
        <Skeleton className="h-4 w-64" />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {Array.from({ length: 4 }).map((_, i) => (
          <Card key={i}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <Skeleton className="h-4 w-24" />
              <Skeleton className="h-4 w-4" />
            </CardHeader>
            <CardContent>
              <Skeleton className="h-8 w-20 mb-2" />
              <Skeleton className="h-3 w-16" />
            </CardContent>
          </Card>
        ))}
      </div>

      <Card className="mb-8">
        <CardHeader>
          <Skeleton className="h-6 w-40" />
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {Array.from({ length: 3 }).map((_, i) => (
              <Skeleton key={i} className="h-16 w-full" />
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

export default function Analytics({ monthlyStats, topPrompts, sellerStats, revenueTrends }: AnalyticsProps) {
  // Ensure all arrays are properly initialized to prevent undefined errors
  const safeMonthlyStats = Array.isArray(monthlyStats) ? monthlyStats : [];
  const safeTopPrompts = Array.isArray(topPrompts) ? topPrompts : [];
  const safeRevenueTrends = Array.isArray(revenueTrends) ? revenueTrends : [];
  const safeSellerStats = sellerStats || {
    total_prompts: 0,
    published_prompts: 0,
    draft_prompts: 0,
    total_sales: 0,
    total_revenue: 0,
    average_rating: 0,
    total_reviews: 0,
    this_month_sales: 0,
    this_month_revenue: 0,
  };

  const formatPrice = (price: any) => {
    return `$${safeToFixed(price)}`;
  };

  const getMonthName = (month: number) => {
    const months = [
      'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
      'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];
    return months[month - 1] || 'Unknown';
  };

  // Use seller stats for overview, fallback to monthly stats calculation with safe array operations
  const totalRevenue = safeSellerStats.total_revenue || safeMonthlyStats.reduce((sum, stat) => sum + (stat?.revenue || 0), 0);
  const totalSales = safeSellerStats.total_sales || safeMonthlyStats.reduce((sum, stat) => sum + (stat?.sales || 0), 0);
  const totalPrompts = safeSellerStats.total_prompts || 0;
  const averageRating = safeSellerStats.average_rating || 0;

  return (
    <AppLayout>
      <Head title="Analytics - CognitPro" />

      <ErrorBoundary
        fallback={
          <div className="container mx-auto px-4 py-8">
            <div className="mb-8">
              <h1 className="text-3xl font-bold mb-2">Analytics</h1>
              <p className="text-muted-foreground">
                Track your performance and sales metrics
              </p>
            </div>
            <Card className="mx-auto max-w-md">
              <CardHeader className="text-center">
                <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/20">
                  <AlertCircle className="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>
                <CardTitle className="text-lg">Unable to Load Analytics</CardTitle>
              </CardHeader>
              <CardContent className="text-center">
                <p className="mb-4 text-sm text-muted-foreground">
                  There was an error loading your analytics data. Please try refreshing the page.
                </p>
              </CardContent>
            </Card>
          </div>
        }
      >
        <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Analytics</h1>
          <p className="text-muted-foreground">
            Track your performance and sales metrics
          </p>
        </div>

        {/* Overview Stats */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(totalRevenue)}</div>
              <p className="text-xs text-muted-foreground">
                Last 12 months
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Sales</CardTitle>
              <TrendingUp className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalSales}</div>
              <p className="text-xs text-muted-foreground">
                Prompts sold
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Avg. Sale Price</CardTitle>
              <BarChart3 className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {formatPrice(totalSales > 0 ? totalRevenue / totalSales : 0)}
              </div>
              <p className="text-xs text-muted-foreground">
                Per prompt
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Prompts</CardTitle>
              <Eye className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{totalPrompts}</div>
              <p className="text-xs text-muted-foreground">
                Published prompts
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Additional Stats */}
        {safeSellerStats && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">This Month</CardTitle>
                <TrendingUp className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{formatPrice(safeSellerStats.this_month_revenue)}</div>
                <p className="text-xs text-muted-foreground">
                  {safeSellerStats.this_month_sales} sales
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Avg. Rating</CardTitle>
                <Eye className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">⭐ {safeToFixed(averageRating, 1)}</div>
                <p className="text-xs text-muted-foreground">
                  {safeSellerStats.total_reviews} reviews
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Published</CardTitle>
                <BarChart3 className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{safeSellerStats.published_prompts}</div>
                <p className="text-xs text-muted-foreground">
                  {safeSellerStats.draft_prompts} drafts
                </p>
              </CardContent>
            </Card>

            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">Conversion</CardTitle>
                <DollarSign className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">
                  {safeSellerStats.published_prompts > 0
                    ? safeToFixed((safeSellerStats.total_sales / safeSellerStats.published_prompts) * 100, 1)
                    : '0.0'
                  }%
                </div>
                <p className="text-xs text-muted-foreground">
                  Sales per prompt
                </p>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Monthly Performance */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Monthly Performance
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {safeMonthlyStats.length > 0 ? (
                safeMonthlyStats.map((stat, index) => (
                  <div key={index} className="flex items-center justify-between p-4 border rounded-lg">
                    <div>
                      <div className="font-medium">
                        {getMonthName(stat?.month)} {stat?.year}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        {stat?.sales || 0} sales
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="font-bold">{formatPrice(stat?.revenue || 0)}</div>
                      <div className="text-sm text-muted-foreground">
                        {formatPrice((stat?.sales || 0) > 0 ? (stat?.revenue || 0) / (stat?.sales || 1) : 0)} avg
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  No sales data available yet
                </div>
              )}
            </div>
          </CardContent>
        </Card>

        {/* Top Performing Prompts */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="h-5 w-5" />
              Top Performing Prompts
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {safeTopPrompts.length > 0 ? (
                safeTopPrompts.map((prompt) => (
                  <div key={prompt?.id || Math.random()} className="flex items-center justify-between p-4 border rounded-lg">
                    <div className="flex-1">
                      <div className="font-medium">{prompt?.title || 'Untitled Prompt'}</div>
                      <div className="text-sm text-muted-foreground">
                        {prompt?.purchases_count || 0} sales
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="font-bold">
                        {formatPrice(prompt?.total_revenue || 0)}
                      </div>
                      <div className="text-sm text-muted-foreground">
                        ⭐ {safeToFixed(prompt?.average_rating || 0, 1)} rating
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-8 text-muted-foreground">
                  No prompt sales data available yet
                </div>
              )}
            </div>
          </CardContent>
        </Card>
        </div>
      </ErrorBoundary>
    </AppLayout>
  );
}
