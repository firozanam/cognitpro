import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import {
    type Category,
    type Prompt,
    type Seller,
    type Tag,
} from '@/types/prompt';
import { Head, useForm } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle,
    CreditCard,
    DollarSign,
    Shield,
    ShoppingCart,
    Sparkles,
    Star,
    User,
} from 'lucide-react';
import { useState } from 'react';

interface Props {
    prompt: CheckoutPrompt;
    platform_fee_rate: number;
}

type CheckoutPrompt = Pick<
    Prompt,
    | 'id'
    | 'title'
    | 'slug'
    | 'description'
    | 'content'
    | 'ai_model'
    | 'price'
    | 'pricing_model'
    | 'min_price'
    | 'rating'
    | 'rating_count'
    | 'purchases_count'
> & {
    category: Category;
    tags: Tag[];
    seller: Seller;
};

export default function Checkout({ prompt, platform_fee_rate }: Props) {
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
            : '',
    );

    const { post, processing, errors, setData } = useForm({
        price:
            prompt.pricing_model === 'fixed'
                ? prompt.price
                : parseFloat(customPrice) || 0,
    });

    const getPrice = () => {
        if (prompt.pricing_model === 'free') return 0;
        if (prompt.pricing_model === 'fixed') return Number(prompt.price);
        return parseFloat(customPrice) || Number(prompt.min_price ?? 0);
    };

    const price = getPrice();
    const platformFee = price * platform_fee_rate;

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
        if (prompt.min_price && enteredPrice < Number(prompt.min_price))
            return false;
        return enteredPrice >= 0;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Checkout - ${prompt.title}`} />

            <div className="container max-w-4xl py-8">
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
                                    Review your purchase before proceeding to
                                    payment
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Prompt Info */}
                                <div className="flex gap-4">
                                    <div className="rounded-lg bg-primary/10 p-4">
                                        <Sparkles className="h-8 w-8 text-primary" />
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="text-lg font-semibold">
                                            {prompt.title}
                                        </h3>
                                        <p className="line-clamp-2 text-sm text-muted-foreground">
                                            {prompt.description}
                                        </p>
                                        <div className="mt-2 flex flex-wrap items-center gap-3">
                                            <Badge variant="secondary">
                                                {prompt.ai_model}
                                            </Badge>
                                            <Badge variant="outline">
                                                {prompt.category.name}
                                            </Badge>
                                            {prompt.rating_count > 0 && (
                                                <div className="flex items-center gap-1 text-sm">
                                                    <Star className="h-4 w-4 fill-warning text-warning" />
                                                    <span>
                                                        {prompt.rating.toFixed(
                                                            1,
                                                        )}
                                                    </span>
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
                                    <div className="rounded-full bg-muted p-2">
                                        <User className="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            Sold by
                                        </p>
                                        <p className="font-medium">
                                            {prompt.seller.name}
                                        </p>
                                    </div>
                                    {prompt.seller.profile?.seller_verified && (
                                        <Badge
                                            variant="secondary"
                                            className="ml-auto"
                                        >
                                            <CheckCircle className="mr-1 h-3 w-3" />
                                            Verified Seller
                                        </Badge>
                                    )}
                                </div>

                                <Separator />

                                {/* Pricing */}
                                <div>
                                    <h4 className="mb-3 font-medium">
                                        Pricing
                                    </h4>
                                    {prompt.pricing_model === 'free' ? (
                                        <div className="flex items-center gap-2 text-success">
                                            <CheckCircle className="h-5 w-5" />
                                            <span className="text-lg font-semibold">
                                                Free
                                            </span>
                                        </div>
                                    ) : prompt.pricing_model === 'fixed' ? (
                                        <div className="text-3xl font-bold">
                                            {formatCurrency(
                                                Number(prompt.price),
                                            )}
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            <p className="text-sm text-muted-foreground">
                                                This prompt uses "Pay What You
                                                Want" pricing.
                                                {prompt.min_price &&
                                                    ` Minimum: ${formatCurrency(Number(prompt.min_price ?? 0))}`}
                                            </p>
                                            <div className="flex items-center gap-2">
                                                <DollarSign className="h-5 w-5 text-muted-foreground" />
                                                <Input
                                                    type="number"
                                                    step="0.01"
                                                    min={prompt.min_price || 0}
                                                    value={customPrice}
                                                    onChange={(e) =>
                                                        setCustomPrice(
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Enter your price"
                                                    className="text-lg"
                                                />
                                            </div>
                                            {prompt.min_price &&
                                                parseFloat(customPrice) <
                                                    Number(
                                                        prompt.min_price,
                                                    ) && (
                                                    <p className="flex items-center gap-1 text-sm text-destructive">
                                                        <AlertCircle className="h-4 w-4" />
                                                        Minimum price is{' '}
                                                        {formatCurrency(
                                                            Number(
                                                                prompt.min_price,
                                                            ),
                                                        )}
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
                                    <span className="text-muted-foreground">
                                        Subtotal
                                    </span>
                                    <span>{formatCurrency(price)}</span>
                                </div>
                                {price > 0 && (
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">
                                            Platform fee
                                        </span>
                                        <span className="text-muted-foreground">
                                            {formatCurrency(platformFee)}
                                        </span>
                                    </div>
                                )}
                                <Separator />
                                <div className="flex justify-between text-lg font-semibold">
                                    <span>Total</span>
                                    <span className="text-success">
                                        {formatCurrency(price)}
                                    </span>
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
                                                <CheckCircle className="mr-2 h-4 w-4" />
                                                Get for Free
                                            </>
                                        ) : (
                                            <>
                                                <CreditCard className="mr-2 h-4 w-4" />
                                                Proceed to Payment
                                            </>
                                        )}
                                    </Button>
                                </form>

                                {errors.price && (
                                    <p className="text-sm text-destructive">
                                        {errors.price}
                                    </p>
                                )}
                            </CardContent>
                            <CardFooter className="flex flex-col gap-2 text-center text-sm text-muted-foreground">
                                <div className="flex items-center justify-center gap-1">
                                    <Shield className="h-4 w-4" />
                                    <span>
                                        Secure checkout powered by Stripe
                                    </span>
                                </div>
                                <p>
                                    By completing this purchase, you agree to
                                    our terms of service.
                                </p>
                            </CardFooter>
                        </Card>

                        {/* What you'll get */}
                        <Card className="mt-4">
                            <CardHeader>
                                <CardTitle className="text-base">
                                    What You'll Get
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ul className="space-y-2">
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-success" />
                                        Full access to the prompt content
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-success" />
                                        Lifetime access
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-success" />
                                        Any future updates
                                    </li>
                                    <li className="flex items-center gap-2 text-sm">
                                        <CheckCircle className="h-4 w-4 text-success" />
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
