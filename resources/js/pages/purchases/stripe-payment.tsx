import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import {
    CreditCard,
    Lock,
    Shield,
    CheckCircle,
    AlertCircle,
    Loader2,
    Sparkles,
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    seller: {
        id: number;
        name: string;
    };
}

interface Purchase {
    id: number;
    order_number: string;
    price: number;
    platform_fee: number;
    seller_earnings: number;
    status: string;
    prompt: Prompt;
}

interface Props {
    purchase: Purchase;
    stripeKey?: string | null;
    intentId?: string | null;
}

export default function StripePayment({ purchase, intentId }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'My Purchases',
            href: '/purchases',
        },
        {
            title: `Order #${purchase.order_number}`,
            href: `/purchases/${purchase.id}`,
        },
        {
            title: 'Payment',
            href: `/purchases/${purchase.id}/stripe-payment`,
        },
    ];

    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    // Demo mode - simulate payment success
    const handleDemoPayment = async () => {
        setProcessing(true);
        setError(null);

        // Simulate processing delay
        await new Promise(resolve => setTimeout(resolve, 2000));

        try {
            const response = await fetch(`/purchases/${purchase.id}/confirm-payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    payment_intent_id: intentId || `demo_${Date.now()}`,
                }),
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                setError(data.message || 'Failed to confirm payment');
            }
        } catch {
            setError('An unexpected error occurred. Please try again.');
        } finally {
            setProcessing(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Payment - Order #${purchase.order_number}`} />

            <div className="container py-8 max-w-2xl">
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                        <CreditCard className="h-8 w-8 text-primary" />
                    </div>
                    <h1 className="text-2xl font-bold">Complete Your Payment</h1>
                    <p className="text-muted-foreground mt-1">
                        Order #{purchase.order_number}
                    </p>
                </div>

                <div className="grid gap-6">
                    {/* Order Summary */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Order Summary</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start gap-3">
                                <div className="p-2 bg-primary/10 rounded-lg">
                                    <Sparkles className="h-5 w-5 text-primary" />
                                </div>
                                <div className="flex-1">
                                    <p className="font-medium">{purchase.prompt.title}</p>
                                    <p className="text-sm text-muted-foreground">
                                        by {purchase.prompt.seller.name}
                                    </p>
                                </div>
                                <p className="font-semibold">{formatCurrency(purchase.price)}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Payment Form */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Lock className="h-5 w-5" />
                                Secure Payment
                            </CardTitle>
                            <CardDescription>
                                Your payment is secured with industry-standard encryption
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* Total */}
                            <div className="flex justify-between items-center">
                                <span className="font-medium">Total</span>
                                <span className="text-2xl font-bold text-green-600">
                                    {formatCurrency(purchase.price)}
                                </span>
                            </div>

                            {error && (
                                <div className="bg-destructive/10 text-destructive p-3 rounded-md flex items-start gap-2">
                                    <AlertCircle className="h-5 w-5 flex-shrink-0 mt-0.5" />
                                    <div>
                                        <p className="font-medium">Payment failed</p>
                                        <p className="text-sm">{error}</p>
                                    </div>
                                </div>
                            )}

                            {/* Demo Notice */}
                            <div className="bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 p-3 rounded-md text-sm">
                                <p className="font-medium">Demo Mode</p>
                                <p>This is a demonstration. In production, Stripe Elements would be used to securely collect card details.</p>
                            </div>
                        </CardContent>
                        <CardFooter className="flex-col gap-4">
                            <Button
                                className="w-full"
                                size="lg"
                                onClick={handleDemoPayment}
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                                        Processing...
                                    </>
                                ) : (
                                    <>
                                        <Lock className="h-4 w-4 mr-2" />
                                        Pay {formatCurrency(purchase.price)}
                                    </>
                                )}
                            </Button>

                            <div className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                                <Shield className="h-4 w-4" />
                                <span>Secured by Stripe</span>
                            </div>
                        </CardFooter>
                    </Card>

                    {/* Security Features */}
                    <div className="grid grid-cols-3 gap-4 text-center">
                        <div className="p-4">
                            <Lock className="h-6 w-6 mx-auto mb-2 text-muted-foreground" />
                            <p className="text-sm font-medium">Encrypted</p>
                            <p className="text-xs text-muted-foreground">SSL Secured</p>
                        </div>
                        <div className="p-4">
                            <Shield className="h-6 w-6 mx-auto mb-2 text-muted-foreground" />
                            <p className="text-sm font-medium">Protected</p>
                            <p className="text-xs text-muted-foreground">Fraud Prevention</p>
                        </div>
                        <div className="p-4">
                            <CheckCircle className="h-6 w-6 mx-auto mb-2 text-muted-foreground" />
                            <p className="text-sm font-medium">Guaranteed</p>
                            <p className="text-xs text-muted-foreground">Money Back</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
