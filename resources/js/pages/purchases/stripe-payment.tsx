import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {
    Elements,
    PaymentElement,
    useElements,
    useStripe,
} from '@stripe/react-stripe-js';
import { loadStripe, type Stripe } from '@stripe/stripe-js';
import {
    AlertCircle,
    CheckCircle,
    CreditCard,
    Loader2,
    Lock,
    Shield,
    Sparkles,
} from 'lucide-react';
import { useMemo, useState } from 'react';

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
    clientSecret?: string | null;
    intentId?: string | null;
}

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);

function PaymentForm({
    purchase,
    intentId,
}: {
    purchase: Purchase;
    intentId?: string | null;
}) {
    const stripe = useStripe();
    const elements = useElements();
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = async (event: React.FormEvent) => {
        event.preventDefault();

        if (!stripe || !elements) {
            return;
        }

        setProcessing(true);
        setError(null);

        const { error: submitError } = await elements.submit();
        if (submitError) {
            setError(submitError.message ?? 'Payment could not be started.');
            setProcessing(false);
            return;
        }

        const { error: confirmError, paymentIntent } =
            await stripe.confirmPayment({
                elements,
                redirect: 'if_required',
            });

        if (confirmError) {
            setError(
                confirmError.message ?? 'Payment failed. Please try again.',
            );
            setProcessing(false);
            return;
        }

        // Server re-verifies intent binding (metadata + amount) before completing.
        try {
            const response = await fetch(
                `/purchases/${purchase.id}/confirm-payment`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') ?? '',
                    },
                    body: JSON.stringify({
                        payment_intent_id: paymentIntent?.id ?? intentId,
                    }),
                },
            );

            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect;
                return;
            }

            setError(data.message || 'Failed to confirm payment');
        } catch {
            setError('An unexpected error occurred. Please try again.');
        } finally {
            setProcessing(false);
        }
    };

    return (
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
                <div className="flex items-center justify-between">
                    <span className="font-medium">Total</span>
                    <span className="text-2xl font-bold text-green-600">
                        {formatCurrency(purchase.price)}
                    </span>
                </div>

                {error && (
                    <div className="flex items-start gap-2 rounded-md bg-destructive/10 p-3 text-destructive">
                        <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0" />
                        <div>
                            <p className="font-medium">Payment failed</p>
                            <p className="text-sm">{error}</p>
                        </div>
                    </div>
                )}

                <PaymentElement />
            </CardContent>
            <CardFooter className="flex-col gap-4">
                <Button
                    className="w-full"
                    size="lg"
                    onClick={handleSubmit}
                    disabled={processing || !stripe || !elements}
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Processing...
                        </>
                    ) : (
                        <>
                            <Lock className="mr-2 h-4 w-4" />
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
    );
}

export default function StripePayment({
    purchase,
    stripeKey,
    clientSecret,
    intentId,
}: Props) {
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

    // Memoized so Elements is not re-created on re-render.
    const stripePromise: Promise<Stripe | null> = useMemo(
        () => (stripeKey ? loadStripe(stripeKey) : Promise.resolve(null)),
        [stripeKey],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Payment - Order #${purchase.order_number}`} />

            <div className="container max-w-2xl py-8">
                <div className="mb-8 text-center">
                    <div className="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                        <CreditCard className="h-8 w-8 text-primary" />
                    </div>
                    <h1 className="text-2xl font-bold">
                        Complete Your Payment
                    </h1>
                    <p className="mt-1 text-muted-foreground">
                        Order #{purchase.order_number}
                    </p>
                </div>

                <div className="grid gap-6">
                    {/* Order Summary */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                Order Summary
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-start gap-3">
                                <div className="rounded-lg bg-primary/10 p-2">
                                    <Sparkles className="h-5 w-5 text-primary" />
                                </div>
                                <div className="flex-1">
                                    <p className="font-medium">
                                        {purchase.prompt.title}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        by {purchase.prompt.seller.name}
                                    </p>
                                </div>
                                <p className="font-semibold">
                                    {formatCurrency(purchase.price)}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Payment Element */}
                    {stripeKey && clientSecret ? (
                        <Elements
                            stripe={stripePromise}
                            options={{
                                clientSecret,
                                appearance: { theme: 'stripe' },
                            }}
                        >
                            <PaymentForm
                                purchase={purchase}
                                intentId={intentId}
                            />
                        </Elements>
                    ) : (
                        <Card>
                            <CardContent className="py-8 text-center text-muted-foreground">
                                <AlertCircle className="mx-auto mb-3 h-8 w-8" />
                                <p className="font-medium text-foreground">
                                    Payment unavailable
                                </p>
                                <p className="text-sm">
                                    Stripe is not configured. Set STRIPE_KEY and
                                    STRIPE_SECRET in your environment, then
                                    retry this purchase.
                                </p>
                            </CardContent>
                        </Card>
                    )}

                    {/* Security Features */}
                    <div className="grid grid-cols-3 gap-4 text-center">
                        <div className="p-4">
                            <Lock className="mx-auto mb-2 h-6 w-6 text-muted-foreground" />
                            <p className="text-sm font-medium">Encrypted</p>
                            <p className="text-xs text-muted-foreground">
                                SSL Secured
                            </p>
                        </div>
                        <div className="p-4">
                            <Shield className="mx-auto mb-2 h-6 w-6 text-muted-foreground" />
                            <p className="text-sm font-medium">Protected</p>
                            <p className="text-xs text-muted-foreground">
                                Fraud Prevention
                            </p>
                        </div>
                        <div className="p-4">
                            <CheckCircle className="mx-auto mb-2 h-6 w-6 text-muted-foreground" />
                            <p className="text-sm font-medium">Guaranteed</p>
                            <p className="text-xs text-muted-foreground">
                                Money Back
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
