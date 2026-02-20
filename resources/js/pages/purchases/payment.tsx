import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
}

export default function Payment({ purchase }: Props) {
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
            href: `/purchases/${purchase.id}/payment`,
        },
    ];

    const [cardNumber, setCardNumber] = useState('');
    const [expiry, setExpiry] = useState('');
    const [cvc, setCvc] = useState('');
    const [cardholderName, setCardholderName] = useState('');

    const { post, processing, errors } = useForm({});

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const formatCardNumber = (value: string) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        const matches = v.match(/\d{4,16}/g);
        const match = (matches && matches[0]) || '';
        const parts = [];
        for (let i = 0, len = match.length; i < len; i += 4) {
            parts.push(match.substring(i, i + 4));
        }
        return parts.length ? parts.join(' ') : v;
    };

    const formatExpiry = (value: string) => {
        const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        if (v.length >= 2) {
            return v.substring(0, 2) + '/' + v.substring(2, 4);
        }
        return v;
    };

    const handleCardNumberChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setCardNumber(formatCardNumber(e.target.value));
    };

    const handleExpiryChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setExpiry(formatExpiry(e.target.value));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        // In a real implementation, this would integrate with Stripe
        // For now, we'll simulate a payment process
        post(`/purchases/${purchase.id}/complete-payment`);
    };

    const isValidForm = () => {
        return (
            cardNumber.replace(/\s/g, '').length >= 15 &&
            expiry.length === 5 &&
            cvc.length >= 3 &&
            cardholderName.length >= 2
        );
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
                                Payment Details
                            </CardTitle>
                            <CardDescription>
                                Your payment information is encrypted and secure
                            </CardDescription>
                        </CardHeader>
                        <form onSubmit={handleSubmit}>
                            <CardContent className="space-y-4">
                                {/* Card Number */}
                                <div className="space-y-2">
                                    <Label htmlFor="cardNumber">Card Number</Label>
                                    <Input
                                        id="cardNumber"
                                        placeholder="1234 5678 9012 3456"
                                        value={cardNumber}
                                        onChange={handleCardNumberChange}
                                        maxLength={19}
                                        className="font-mono"
                                    />
                                </div>

                                {/* Cardholder Name */}
                                <div className="space-y-2">
                                    <Label htmlFor="cardholderName">Cardholder Name</Label>
                                    <Input
                                        id="cardholderName"
                                        placeholder="John Doe"
                                        value={cardholderName}
                                        onChange={(e) => setCardholderName(e.target.value)}
                                    />
                                </div>

                                {/* Expiry and CVC */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="expiry">Expiry Date</Label>
                                        <Input
                                            id="expiry"
                                            placeholder="MM/YY"
                                            value={expiry}
                                            onChange={handleExpiryChange}
                                            maxLength={5}
                                            className="font-mono"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="cvc">CVC</Label>
                                        <Input
                                            id="cvc"
                                            placeholder="123"
                                            value={cvc}
                                            onChange={(e) => setCvc(e.target.value.replace(/\D/g, '').substring(0, 4))}
                                            maxLength={4}
                                            className="font-mono"
                                        />
                                    </div>
                                </div>

                                {/* Total */}
                                <Separator />
                                <div className="flex justify-between items-center">
                                    <span className="font-medium">Total</span>
                                    <span className="text-2xl font-bold text-green-600">
                                        {formatCurrency(purchase.price)}
                                    </span>
                                </div>

                                {errors && Object.keys(errors).length > 0 && (
                                    <div className="bg-destructive/10 text-destructive p-3 rounded-md flex items-start gap-2">
                                        <AlertCircle className="h-5 w-5 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p className="font-medium">Payment failed</p>
                                            <p className="text-sm">Please check your card details and try again.</p>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                            <CardFooter className="flex-col gap-4">
                                <Button
                                    type="submit"
                                    className="w-full"
                                    size="lg"
                                    disabled={processing || !isValidForm()}
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

                                <p className="text-xs text-center text-muted-foreground">
                                    This is a demo. In production, this would integrate with Stripe.js for secure payment processing.
                                </p>
                            </CardFooter>
                        </form>
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
