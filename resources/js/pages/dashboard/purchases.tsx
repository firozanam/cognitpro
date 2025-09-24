import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { 
  ShoppingBag, 
  Download, 
  Calendar, 
  User,
  FileText,
  Star,
  Eye
} from 'lucide-react';
import { safeToFixed } from '@/lib/utils';

interface PurchasesProps {
  purchases: {
    data: Array<{
      id: number;
      price_paid: number;
      created_at: string;
      prompt: {
        id: number;
        title: string;
        description: string;
        uuid: string;
        category: {
          id: number;
          name: string;
        };
        user: {
          id: number;
          name: string;
        };
      };
    }>;
    links: any;
    meta: any;
  };
}

export default function Purchases({ purchases }: PurchasesProps) {
  const formatPrice = (price: any) => {
    return `$${safeToFixed(price)}`;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
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

  const totalSpent = purchases.data.reduce((sum, purchase) => sum + purchase.price_paid, 0);

  return (
    <AppLayout>
      <Head title="My Purchases - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold mb-2">My Purchases</h1>
            <p className="text-muted-foreground">
              Access and manage your purchased prompts
            </p>
          </div>
          <Button asChild>
            <Link href="/prompts">
              <ShoppingBag className="h-4 w-4 mr-2" />
              Browse More Prompts
            </Link>
          </Button>
        </div>

        {/* Purchase Summary */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Purchases</CardTitle>
              <ShoppingBag className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{purchases.data.length}</div>
              <p className="text-xs text-muted-foreground">
                Prompts owned
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Spent</CardTitle>
              <FileText className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(totalSpent)}</div>
              <p className="text-xs text-muted-foreground">
                On prompts
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Average Price</CardTitle>
              <Star className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">
                {formatPrice(purchases.data.length > 0 ? totalSpent / purchases.data.length : 0)}
              </div>
              <p className="text-xs text-muted-foreground">
                Per prompt
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Purchases List */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Your Purchased Prompts
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {purchases.data.length > 0 ? (
                purchases.data.map((purchase) => (
                  <div key={purchase.id} className="border rounded-lg p-6 hover:bg-muted/50 transition-colors">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-3 mb-2">
                          <Link 
                            href={`/prompts/${purchase.prompt.uuid}`}
                            className="text-lg font-semibold hover:underline"
                          >
                            {purchase.prompt.title}
                          </Link>
                          <Badge variant="secondary">
                            {purchase.prompt.category.name}
                          </Badge>
                        </div>
                        
                        <p className="text-muted-foreground mb-3 line-clamp-2">
                          {purchase.prompt.description}
                        </p>
                        
                        <div className="flex items-center gap-4 text-sm text-muted-foreground">
                          <div className="flex items-center gap-1">
                            <User className="h-4 w-4" />
                            <span>by {purchase.prompt.user.name}</span>
                          </div>
                          <div className="flex items-center gap-1">
                            <Calendar className="h-4 w-4" />
                            <span>Purchased {formatDate(purchase.created_at)}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div className="text-right ml-6">
                        <div className="text-lg font-bold mb-2">
                          {formatPrice(purchase.price_paid)}
                        </div>
                        <div className="flex gap-2">
                          <Button size="sm" asChild>
                            <Link href={`/prompts/${purchase.prompt.uuid}`}>
                              <Eye className="h-4 w-4 mr-1" />
                              View
                            </Link>
                          </Button>
                          <Button size="sm" variant="outline">
                            <Download className="h-4 w-4 mr-1" />
                            Download
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-12">
                  <ShoppingBag className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium mb-2">No purchases yet</h3>
                  <p className="text-muted-foreground mb-6">
                    Start exploring our marketplace to find the perfect prompts for your needs.
                  </p>
                  <div className="flex gap-4 justify-center">
                    <Button asChild>
                      <Link href="/prompts">
                        <ShoppingBag className="h-4 w-4 mr-2" />
                        Browse Prompts
                      </Link>
                    </Button>
                    <Button variant="outline" asChild>
                      <Link href="/categories">
                        <FileText className="h-4 w-4 mr-2" />
                        Explore Categories
                      </Link>
                    </Button>
                  </div>
                </div>
              )}
            </div>

            {/* Pagination would go here if needed */}
            {purchases.links && (
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
