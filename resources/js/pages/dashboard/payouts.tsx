import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
  CreditCard, 
  DollarSign, 
  Calendar, 
  AlertCircle,
  CheckCircle,
  Clock
} from 'lucide-react';
import { safeToFixed } from '@/lib/utils';

interface PayoutsProps {
  payouts: Array<{
    id: number;
    amount: number;
    status: 'pending' | 'processed' | 'failed';
    created_at: string;
    processed_at?: string;
  }>;
  pendingEarnings: number;
}

export default function Payouts({ payouts, pendingEarnings }: PayoutsProps) {
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

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'processed':
        return <CheckCircle className="h-4 w-4 text-green-500" />;
      case 'pending':
        return <Clock className="h-4 w-4 text-yellow-500" />;
      case 'failed':
        return <AlertCircle className="h-4 w-4 text-red-500" />;
      default:
        return <Clock className="h-4 w-4 text-gray-500" />;
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'processed':
        return <Badge variant="default" className="bg-green-100 text-green-800">Processed</Badge>;
      case 'pending':
        return <Badge variant="secondary" className="bg-yellow-100 text-yellow-800">Pending</Badge>;
      case 'failed':
        return <Badge variant="destructive">Failed</Badge>;
      default:
        return <Badge variant="secondary">Unknown</Badge>;
    }
  };

  const totalProcessed = payouts
    .filter(payout => payout.status === 'processed')
    .reduce((sum, payout) => sum + payout.amount, 0);

  const totalPending = payouts
    .filter(payout => payout.status === 'pending')
    .reduce((sum, payout) => sum + payout.amount, 0);

  return (
    <AppLayout>
      <Head title="Payouts - CognitPro" />

      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Payouts</h1>
          <p className="text-muted-foreground">
            Manage your earnings and payout history
          </p>
        </div>

        {/* Earnings Overview */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Earnings</CardTitle>
              <DollarSign className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(pendingEarnings)}</div>
              <p className="text-xs text-muted-foreground">
                Available for payout
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Total Paid Out</CardTitle>
              <CheckCircle className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(totalProcessed)}</div>
              <p className="text-xs text-muted-foreground">
                Successfully processed
              </p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm font-medium">Pending Payouts</CardTitle>
              <Clock className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold">{formatPrice(totalPending)}</div>
              <p className="text-xs text-muted-foreground">
                Being processed
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Payout Actions */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="h-5 w-5" />
              Request Payout
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between">
              <div>
                <div className="font-medium">Available Balance</div>
                <div className="text-2xl font-bold text-green-600">
                  {formatPrice(pendingEarnings)}
                </div>
                <div className="text-sm text-muted-foreground mt-1">
                  Minimum payout amount: $25.00
                </div>
              </div>
              <div>
                <Button 
                  disabled={pendingEarnings < 25}
                  className="bg-primary hover:bg-primary/90"
                >
                  Request Payout
                </Button>
                {pendingEarnings < 25 && (
                  <p className="text-xs text-muted-foreground mt-2">
                    Minimum payout amount not reached
                  </p>
                )}
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Payout History */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Payout History
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {payouts.length > 0 ? (
                payouts.map((payout) => (
                  <div key={payout.id} className="flex items-center justify-between p-4 border rounded-lg">
                    <div className="flex items-center gap-4">
                      {getStatusIcon(payout.status)}
                      <div>
                        <div className="font-medium">
                          Payout #{payout.id}
                        </div>
                        <div className="text-sm text-muted-foreground">
                          Requested: {formatDate(payout.created_at)}
                          {payout.processed_at && (
                            <span> • Processed: {formatDate(payout.processed_at)}</span>
                          )}
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        <div className="font-bold text-lg">
                          {formatPrice(payout.amount)}
                        </div>
                      </div>
                      {getStatusBadge(payout.status)}
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-12">
                  <CreditCard className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <h3 className="text-lg font-medium mb-2">No payouts yet</h3>
                  <p className="text-muted-foreground mb-4">
                    Your payout history will appear here once you request your first payout.
                  </p>
                  <div className="text-sm text-muted-foreground">
                    <p>• Payouts are processed monthly</p>
                    <p>• Minimum payout amount is $25.00</p>
                    <p>• Processing typically takes 3-5 business days</p>
                  </div>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}
