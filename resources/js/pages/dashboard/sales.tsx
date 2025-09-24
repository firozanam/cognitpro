import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { 
  DollarSign, 
  Calendar, 
  User,
  FileText,
  Eye
} from 'lucide-react';
import { safeToFixed } from '@/lib/utils';

interface SalesProps {
  sales: {
    data: Array<{
      id: number;
      price_paid: number;
      created_at: string;
      user: {
        id: number;
        name: string;
        email: string;
      };
      prompt: {
        id: number;
        title: string;
        uuid: string;
      };
    }>;
    links: any;
    meta: any;
  };
}

export default function Sales({ sales }: SalesProps) {
  const formatPrice = (price: any) => {
    return `$${safeToFixed(price)}`;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getUserInitials = (name: string) => {
    return name
      .split(' ')
      .map(word => word.charAt(0))
      .join('')
      .toUpperCase()
      .slice(0, 2);
  };

  const totalRevenue = sales.data.reduce((sum, sale) => sum + sale.price_paid, 0);

  return (
    <AppLayout>
      <Head title="Sales & Revenue - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Sales & Revenue</h1>
          <p className="text-muted-foreground">
            Track all your prompt sales and earnings
          </p>
        </div>

        {/* Summary Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(totalRevenue)}</div>
              <p className="text-xs text-muted-foreground">
                From {sales.data.length} sales
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Sales</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{sales.data.length}</div>
              <p className="text-xs text-muted-foreground">
                Prompts sold
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Average Sale</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {formatPrice(sales.data.length > 0 ? totalRevenue / sales.data.length : 0)}
              </div>
              <p className="text-xs text-muted-foreground">
                Per prompt
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Sales List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Recent Sales
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {sales.data.length > 0 ? (
                sales.data.map((sale) => (
                  <div key={sale.id} className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                    <div className="flex items-center gap-4">
                      <Avatar>
                        <AvatarImage src={`https://api.dicebear.com/7.x/initials/svg?seed=${sale.user.name}`} />
                        <AvatarFallback>
                          {getUserInitials(sale.user.name)}
                        </AvatarFallback>
                      </Avatar>
                      
                      <div>
                        <div className="font-medium">{sale.user.name}</div>
                        <div className="text-sm text-muted-foreground">
                          {sale.user.email}
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        <Link 
                          href={`/prompts/${sale.prompt.uuid}`}
                          className="font-medium hover:underline"
                        >
                          {sale.prompt.title}
                        </Link>
                        <div className="text-sm text-muted-foreground">
                          {formatDate(sale.created_at)}
                        </div>
                      </div>

                      <div className="text-right">
                        <div className="font-bold text-lg">{formatPrice(sale.price_paid)}</div>
                        <Badge variant="secondary">
                          Sale
                        </Badge>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-12">
                  <FileText className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium mb-2">No sales yet</h3>
                  <p className="text-muted-foreground mb-4">
                    Start creating and publishing prompts to see your sales here.
                  </p>
                  <Link 
                    href="/prompts/create"
                    className="inline-flex items-center gap-2 bg-primary text-primary-foreground px-4 py-2 rounded-md hover:bg-primary/90 transition-colors"
                  >
                    <FileText className="h-4 w-4" />
                    Create Your First Prompt
                  </Link>
                </div>
              )}
            </div>

            {/* Pagination would go here if needed */}
            {sales.links && (
              <div className="mt-6 flex justify-center">
                {/* Add pagination component here */}
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
