import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import {
    ShoppingCart,
    Trash2,
    CreditCard,
    Shield,
    CheckCircle,
    Star,
    Sparkles,
    ArrowRight,
} from 'lucide-react';
import { type BreadcrumbItem } from '@/types';
import { useState } from 'react';

interface Prompt {
    id: number;
    title: string;
    slug: string;
    description: string;
    ai_model: string;
    price: number;
    pricing_model: string;
    rating: number;
    rating_count: number;
    seller: {
        id: number;
        name: string;
    };
    category: {
        id: number;
        name: string;
    };
}

interface CartItem {
    id: number;
    price: number;
    prompt: Prompt;
}

interface Props {
    cartItems: CartItem[];
    total: number;
    count: number;
}

export default function CartIndex({ cartItems, total, count }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Shopping Cart',
            href: '/cart',
        },
    ];

    const [removingId, setRemovingingId] = useState<number | null>(null);

    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }).format(amount);
    };

    const handleRemoveItem = (cartId: number) => {
        setRemovingingId(cartId);
        router.delete(`/cart/${cartId}`, {
            onFinish: () => setRemovingingId(null),
        });
    };

    const handleClearCart = () => {
        if (confirm('Are you sure you want to clear your cart?')) {
            router.delete('/cart');
        }
    };

    const platformFee = total * 0.15;
    const grandTotal = total;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shopping Cart" />

            <div className="container py-8">
                {/* Header */}
                <div className="flex items-center justify-between mb-8">
                    <div>
                        <h1 className="text-3xl font-bold flex items-center gap-3">
                            <ShoppingCart className="h-8 w-8" />
                            Shopping Cart
                        </h1>
                        <p className="text-muted-foreground mt-1">
                            {count === 0 ? 'Your cart is empty' : `${count} item${count !== 1 ? 's' : ''} in your cart`}
                        </p>
                    </div>
                    {count > 0 && (
                        <Button variant="outline" onClick={handleClearCart}>
                            <Trash2 className="h-4 w-4 mr-2" />
                            Clear Cart
                        </Button>
                    )}
                </div>

                {cartItems.length > 0 ? (
                    <div className="grid gap-8 lg:grid-cols-3">
                        {/* Cart Items */}
                        <div className="lg:col-span-2 space-y-4">
                            {cartItems.map((item) => (
                                <Card key={item.id}>
                                    <CardContent className="p-6">
                                        <div className="flex gap-4">
                                            <div className="p-4 bg-primary/10 rounded-lg">
                                                <Sparkles className="h-8 w-8 text-primary" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <Link
                                                            href={`/prompts/${item.prompt.slug}`}
                                                            className="font-semibold text-lg hover:text-primary"
                                                        >
                                                            {item.prompt.title}
                                                        </Link>
                                                        <p className="text-sm text-muted-foreground line-clamp-2 mt-1">
                                                            {item.prompt.description}
                                                        </p>
                                                    </div>
                                                    <p className="text-xl font-bold text-green-600">
                                                        {formatCurrency(item.prompt.price)}
                                                    </p>
                                                </div>

                                                <div className="flex flex-wrap items-center gap-3 mt-3">
                                                    <Badge variant="secondary">{item.prompt.ai_model}</Badge>
                                                    <Badge variant="outline">{item.prompt.category.name}</Badge>
                                                    {item.prompt.rating_count > 0 && (
                                                        <div className="flex items-center gap-1 text-sm">
                                                            <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                                            <span>{item.prompt.rating.toFixed(1)}</span>
                                                            <span className="text-muted-foreground">
                                                                ({item.prompt.rating_count})
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="flex items-center justify-between mt-4">
                                                    <p className="text-sm text-muted-foreground">
                                                        by <span className="font-medium">{item.prompt.seller.name}</span>
                                                    </p>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() => handleRemoveItem(item.id)}
                                                        disabled={removingId === item.id}
                                                    >
                                                        <Trash2 className="h-4 w-4 mr-1" />
                                                        Remove
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>

                        {/* Order Summary */}
                        <div className="lg:col-span-1">
                            <Card className="sticky top-8">
                                <CardHeader>
                                    <CardTitle>Order Summary</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">Subtotal ({count} items)</span>
                                        <span>{formatCurrency(total)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">Platform fee</span>
                                        <span className="text-muted-foreground">
                                            {formatCurrency(platformFee)}
                                        </span>
                                    </div>
                                    <Separator />
                                    <div className="flex justify-between font-semibold text-lg">
                                        <span>Total</span>
                                        <span className="text-green-600">{formatCurrency(grandTotal)}</span>
                                    </div>

                                    <Button className="w-full" size="lg" asChild>
                                        <Link href="/checkout/cart">
                                            <CreditCard className="h-4 w-4 mr-2" />
                                            Proceed to Checkout
                                            <ArrowRight className="h-4 w-4 ml-2" />
                                        </Link>
                                    </Button>

                                    <Button variant="outline" className="w-full" asChild>
                                        <Link href="/marketplace">
                                            Continue Shopping
                                        </Link>
                                    </Button>
                                </CardContent>
                                <CardFooter className="flex flex-col gap-2 text-center text-sm text-muted-foreground">
                                    <div className="flex items-center justify-center gap-1">
                                        <Shield className="h-4 w-4" />
                                        <span>Secure checkout</span>
                                    </div>
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
                                            Full access to prompt content
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-green-500" />
                                            Lifetime access
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-green-500" />
                                            Future updates included
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-green-500" />
                                            Ability to leave reviews
                                        </li>
                                    </ul>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                ) : (
                    <Card>
                        <CardContent className="py-16 text-center">
                            <ShoppingCart className="h-16 w-16 text-muted-foreground mx-auto mb-4" />
                            <h3 className="text-xl font-semibold mb-2">Your cart is empty</h3>
                            <p className="text-muted-foreground mb-6 max-w-md mx-auto">
                                Looks like you haven't added any prompts to your cart yet. Browse the marketplace to find prompts that suit your needs.
                            </p>
                            <Button asChild>
                                <Link href="/marketplace">
                                    <Sparkles className="h-4 w-4 mr-2" />
                                    Browse Marketplace
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
