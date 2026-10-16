import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import PublicLayout from '@/layouts/public-layout';
import { formatCurrency } from '@/lib/format';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { type Prompt } from '@/types/prompt';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    CheckCircle,
    CreditCard,
    Shield,
    ShoppingCart,
    Sparkles,
    Star,
    Trash2,
} from 'lucide-react';
import { useState } from 'react';

type CartPrompt = Pick<
    Prompt,
    | 'id'
    | 'title'
    | 'slug'
    | 'description'
    | 'ai_model'
    | 'price'
    | 'pricing_model'
    | 'rating'
    | 'rating_count'
> & {
    seller: { id: number; name: string };
    category: { id: number; name: string };
};

interface CartItem {
    id: number;
    price: number;
    prompt: CartPrompt;
}

interface Props {
    cartItems: CartItem[];
    total: number;
    count: number;
}

export default function CartIndex({ cartItems, total, count }: Props) {
    const { auth } = usePage<SharedData>().props;
    const isAuthenticated = auth?.user != null;

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Shopping Cart',
            href: '/cart',
        },
    ];

    const [removingId, setRemovingId] = useState<number | null>(null);
    const [checkingOut, setCheckingOut] = useState(false);

    const handleRemoveItem = (cartId: number) => {
        setRemovingId(cartId);
        router.delete(`/cart/${cartId}`, {
            onFinish: () => setRemovingId(null),
        });
    };

    const handleClearCart = () => {
        if (confirm('Are you sure you want to clear your cart?')) {
            router.delete('/cart');
        }
    };

    const handleCheckout = () => {
        setCheckingOut(true);
        router.post(
            '/cart/checkout',
            {},
            {
                onFinish: () => setCheckingOut(false),
            },
        );
    };

    const platformFee = total * 0.15;

    const pageContent = (
        <>
            <Head title="Shopping Cart" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-3 text-2xl font-bold tracking-tight">
                            <ShoppingCart className="h-6 w-6" />
                            Shopping Cart
                        </h1>
                        <p className="text-muted-foreground">
                            {count === 0
                                ? 'Your cart is empty'
                                : `${count} item${count !== 1 ? 's' : ''} in your cart`}
                        </p>
                    </div>
                    {count > 0 && (
                        <Button variant="outline" onClick={handleClearCart}>
                            <Trash2 className="mr-2 h-4 w-4" />
                            Clear Cart
                        </Button>
                    )}
                </div>

                {cartItems.length > 0 ? (
                    <div className="grid gap-4 lg:grid-cols-3">
                        {/* Cart Items */}
                        <div className="space-y-4 lg:col-span-2">
                            {cartItems.map((item) => (
                                <Card key={item.id}>
                                    <CardContent className="p-6">
                                        <div className="flex gap-4">
                                            <div className="rounded-lg bg-primary/10 p-4">
                                                <Sparkles className="h-8 w-8 text-primary" />
                                            </div>
                                            <div className="flex-1">
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <Link
                                                            href={`/prompts/${item.prompt.slug}`}
                                                            className="text-lg font-semibold hover:text-primary"
                                                        >
                                                            {item.prompt.title}
                                                        </Link>
                                                        <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                                            {
                                                                item.prompt
                                                                    .description
                                                            }
                                                        </p>
                                                    </div>
                                                    <p className="text-xl font-bold text-success">
                                                        {formatCurrency(
                                                            Number(
                                                                item.prompt
                                                                    .price,
                                                            ),
                                                        )}
                                                    </p>
                                                </div>

                                                <div className="mt-3 flex flex-wrap items-center gap-3">
                                                    <Badge variant="secondary">
                                                        {item.prompt.ai_model}
                                                    </Badge>
                                                    <Badge variant="outline">
                                                        {
                                                            item.prompt.category
                                                                .name
                                                        }
                                                    </Badge>
                                                    {item.prompt.rating_count >
                                                        0 && (
                                                        <div className="flex items-center gap-1 text-sm">
                                                            <Star className="h-4 w-4 fill-warning text-warning" />
                                                            <span>
                                                                {item.prompt.rating.toFixed(
                                                                    1,
                                                                )}
                                                            </span>
                                                            <span className="text-muted-foreground">
                                                                (
                                                                {
                                                                    item.prompt
                                                                        .rating_count
                                                                }
                                                                )
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>

                                                <div className="mt-4 flex items-center justify-between">
                                                    <p className="text-sm text-muted-foreground">
                                                        by{' '}
                                                        <span className="font-medium">
                                                            {
                                                                item.prompt
                                                                    .seller.name
                                                            }
                                                        </span>
                                                    </p>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="text-destructive hover:text-destructive"
                                                        onClick={() =>
                                                            handleRemoveItem(
                                                                item.id,
                                                            )
                                                        }
                                                        disabled={
                                                            removingId ===
                                                            item.id
                                                        }
                                                    >
                                                        <Trash2 className="mr-1 h-4 w-4" />
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
                                        <span className="text-muted-foreground">
                                            Subtotal ({count} items)
                                        </span>
                                        <span>{formatCurrency(total)}</span>
                                    </div>
                                    <div className="flex justify-between text-sm">
                                        <span className="text-muted-foreground">
                                            Platform fee
                                        </span>
                                        <span className="text-muted-foreground">
                                            {formatCurrency(platformFee)}
                                        </span>
                                    </div>
                                    <Separator />
                                    <div className="flex justify-between text-lg font-semibold">
                                        <span>Total</span>
                                        <span className="text-success">
                                            {formatCurrency(total)}
                                        </span>
                                    </div>

                                    <Button
                                        className="w-full"
                                        size="lg"
                                        onClick={handleCheckout}
                                        disabled={checkingOut}
                                    >
                                        <CreditCard className="mr-2 h-4 w-4" />
                                        {checkingOut
                                            ? 'Processing...'
                                            : 'Proceed to Checkout'}
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Button>

                                    <Button
                                        variant="outline"
                                        className="w-full"
                                        asChild
                                    >
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
                                    <CardTitle className="text-base">
                                        What You'll Get
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul className="space-y-2">
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-success" />
                                            Full access to prompt content
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-success" />
                                            Lifetime access
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-success" />
                                            Future updates included
                                        </li>
                                        <li className="flex items-center gap-2 text-sm">
                                            <CheckCircle className="h-4 w-4 text-success" />
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
                            <ShoppingCart className="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                            <h3 className="mb-2 text-xl font-semibold">
                                Your cart is empty
                            </h3>
                            <p className="mx-auto mb-6 max-w-md text-muted-foreground">
                                Looks like you haven't added any prompts to your
                                cart yet. Browse the marketplace to find prompts
                                that suit your needs.
                            </p>
                            <Button asChild>
                                <Link href="/marketplace">
                                    <Sparkles className="mr-2 h-4 w-4" />
                                    Browse Marketplace
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );

    // Authenticated users get the sidebar layout; guests get the public layout
    if (isAuthenticated) {
        return <AppLayout breadcrumbs={breadcrumbs}>{pageContent}</AppLayout>;
    }

    return <PublicLayout>{pageContent}</PublicLayout>;
}
