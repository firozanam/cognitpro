import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Input } from '@/components/ui/input';
import {
    ShoppingCart,
    CreditCard,
    Shield,
    CheckCircle,
    Star,
    User,
    Sparkles,
    DollarSign,
    AlertCircle,
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Tag {
    id: number;
    name: string;
    slug: string;
}

interface Seller {
    id: number;
    name: string;
    profile?: {
        bio?: string;
        seller_verified?: boolean;
        total_sales?: number;
    };
}

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    content: string;
    ai_model: string;
    price: number;
    pricing_model: string;
    min_price?: number;
    rating: number;
    rating_count: number;
    purchases_count: number;
    category: Category;
    tags: Tag[];
    seller: Seller;
}

interface Props {
    prompt: Prompt;
}

export default function Checkout({ prompt }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Marketplace',
            href: '/marketplace',
        },
        {
            title: prompt.title,
            href: `/prompts/${prompt.slug}`,
        },
        {
            title: 'Checkout',
            href: `/checkout/${prompt.id}`,
        },
    ];

    const [customPrice, setCustomPrice] = useState<string>(
        prompt.pricing_model === 'pay_what_you_want' && prompt.min_price
            ? prompt.min_price.toString()
            : ''
    );

    const { post, processing, errors, setData } = useForm({
        price: prompt.pricing_model === 'fixed' ? prompt.price : parseFloat(customPrice) || 0,
    });

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const getPrice = () => {
        if (prompt.pricing_model === 'free') return 0;
        if (prompt.pricing_model === 'fixed') return prompt.price;
        return parseFloat(customPrice) || prompt.min_price || 0;
    };

    const price = getPrice();
    const platformFee = price * 0.15;

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        setData('price', price);
        post(`/purchase/${prompt.id}`);
    };

    const isPriceValid = () => {
        if (prompt.pricing_model === 'free') return true;
        if (prompt.pricing_model === 'fixed') return true;
        const enteredPrice = parseFloat(customPrice);
        if (isNaN(enteredPrice)) return false;
        if (prompt.min_price && enteredPrice < prompt.min_price) return false;
        return enteredPrice >= 0;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Checkout - ${prompt.title}`} />

            <div className="container py-8 max-w-4xl">
                <div className="grid gap-8 md:grid-cols-5">
                    {/* Order Summary */}
                    <div className="md:col-span-3">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <ShoppingCart className="h-5 w-5" />
                                    Order Summary
                                </CardTitle>
                                <CardDescription>
                                    Review your purchase before proceeding to payment
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Prompt Info */}
                                <div className="flex gap-4">
                                    <div className="p-4 bg-primary/10 rounded-lg">
                                        <Sparkles className="h-8 w-8 text-primary" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold text-lg">{prompt.title}</h3>
                                        <p className="text-sm text-muted-foreground line-clamp-2">
                                            {prompt.description}
                                        </p>
                                        <div className="flex flex-wrap items-center gap-3 mt-2">
                                            <Badge variant="secondary">{prompt.ai_model}</Badge>
                                            <Badge variant="outline">{prompt.category.name}</Badge>
                                            {prompt.rating_count > 0 && (
                                                <div className="flex items-center gap-1 text-sm">
                                                    <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                    <span>{prompt.rating.toFixed(1)}</span>
                                                    <span className="text-muted-foreground">
                                                        ({prompt.rating_count})
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                {/* Seller Info */}
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-muted rounded-full">
                                        <User className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">Sold by</p>
                                        <p className="font-medium">{prompt.seller.name}</p>
                                    </div>
                                    {prompt.seller.profile?.seller_verified && (
                                        <Badge variant="secondary" className="ml-auto">
                                            <CheckCircle className="h-3 w-3 mr-1" />
                                            Verified Seller
                                        </Badge>
                                    )}
                                </div>

                                <Separator />

                                {/* Pricing */}
                                <div>
                                    <h4 className="font-medium mb-3">Pricing</h4>
                                    {prompt.pricing_model === 'free' ? (
                                        <div className="flex items-center gap-2 text-green-600">
                                            <CheckCircle className="h-5 w-5" />
                                            <span className="text-lg font-semibold">Free</span>
                                        </div>
                                    ) : prompt.pricing_model === 'fixed' ? (
                                        <div className="text-3xl font-bold">
                                            {formatCurrency(prompt.price)}
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            <p className="text-sm text-muted-foreground">
                                                This prompt uses "Pay What You Want" pricing.
                                                {prompt.min_price && ` Minimum: ${formatCurrency(prompt.min_price)}`}
                                            </p>
                                            <div className="flex items-center gap-2">
                                                <DollarSign className="h-5 w-5 text-muted-foreground" />
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    min={prompt.min_price || 0}
                                                    value={customPrice}
                                                    onChange={(e) => setCustomPrice(e.target.value)}
                                                    placeholder="Enter your price"
                                                    className="text-lg"
                                                />
                                            </div>
                                            {prompt.min_price && parseFloat(customPrice) < prompt.min_price && (
                                                <p className="text-sm text-destructive flex items-center gap-1">
                                                    <AlertCircle className="h-4 w-4" />
                                                    Minimum price is {formatCurrency(prompt.min_price)}
                                                </p>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Payment Summary */}
                    <div className="md:col-span-2">
                        <Card className="sticky top-8">
                            <CardHeader>
                                <CardTitle>Payment Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">Subtotal</span>
                                    <span>{formatCurrency(price)}</span>
                                </div>
                                {price > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Platform fee</span>
                                        <span className="text-muted-foreground">
                                            {formatCurrency(platformFee)}
                                        </span>
                                    </div>
                                )}
                                <Separator />
                                <div className="flex justify-between font-semibold text-lg">
                                    <span>Total</span>
                                    <span className="text-green-600">{formatCurrency(price)}</span>
                                </div>

                                <form onSubmit={handleSubmit}>
                                    <Button
                                        type="submit"
                                        className="w-full"
                                        size="lg"
                                        disabled={processing || !isPriceValid()}
                                    >
                                        {prompt.pricing_model === 'free' ? (
                                            <>
                                                <CheckCircle className="h-4 w-4 mr-2" />
                                                Get for Free
                                            </>
                                        ) : (
                                            <>
                                                <CreditCard className="h-4 w-4 mr-2" />
                                                Proceed to Payment
                                            </>
                                        )}
                                    </Button>
                                </form>

                                {errors.price && (
                                    <p className="text-sm text-destructive">{errors.price}</p>
                                )}
                            </CardContent>
                            <CardFooter className="flex flex-col gap-2 text-center text-sm text-muted-foreground">
                                <div className="flex items-center justify-center gap-1">
                                    <Shield className="h-4 w-4" />
                                    <span>Secure checkout powered by Stripe</span>
                                </div>
                                <p>
                                    By completing this purchase, you agree to our terms of service.
                                </p>
                            </CardFooter>
                        </Card>

                        {/* What you'll get */}
                        <Card className="mt-4">
                            <CardHeader>
                                <CardTitle className="text-base">What You'll Get</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ul className="space-y-2">
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                        Full access to the prompt content
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                        Lifetime access
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                        Any future updates
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                        Ability to leave a review
                                    </li>
                                </ul>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
