import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Bot,
    Check,
    ChevronRight,
    Code,
    FileText,
    MessageSquare,
    Palette,
    Search,
    Shield,
    ShoppingCart,
    Sparkles,
    Star,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-react';

export default function Home({
    canRegister = true,
    featuredPrompts: _featuredPrompts = [],
    categories: _categories = [],
    stats = { prompts: 1250, sellers: 450, sales: 15000 },
}: {
    canRegister?: boolean;
    featuredPrompts?: Array<{
        id: number;
        title: string;
        description: string;
        price: number;
        rating: number;
        ai_model: string;
        seller: { name: string };
    }>;
    categories?: Array<{
        id: number;
        name: string;
        icon: string;
        count: number;
    }>;
    stats?: { prompts: number; sellers: number; sales: number };
}) {
    // These will be used when backend data is available
    void _featuredPrompts;
    void _categories;
    const { auth, cartCount } = usePage<SharedData>().props;

    const aiModels = [
        { name: 'ChatGPT', icon: Bot, color: 'text-success' },
        { name: 'Midjourney', icon: Palette, color: 'text-chart-5' },
        { name: 'DALL-E', icon: Sparkles, color: 'text-info' },
        { name: 'Claude', icon: MessageSquare, color: 'text-chart-4' },
        { name: 'Stable Diffusion', icon: Palette, color: 'text-chart-5' },
        { name: 'GitHub Copilot', icon: Code, color: 'text-gray-500' },
    ];

    const features = [
        {
            icon: Shield,
            title: 'Quality Verified',
            description: 'Every prompt is reviewed and tested before approval',
        },
        {
            icon: Zap,
            title: 'Instant Access',
            description: 'Download your prompts immediately after purchase',
        },
        {
            icon: TrendingUp,
            title: 'Seller Analytics',
            description: 'Track your sales, views, and earnings in real-time',
        },
        {
            icon: Users,
            title: 'Global Community',
            description: 'Connect with prompt engineers from around the world',
        },
    ];

    const howItWorks = [
        {
            step: 1,
            title: 'Browse & Search',
            description:
                'Explore thousands of prompts across multiple AI platforms',
            icon: Search,
        },
        {
            step: 2,
            title: 'Preview & Test',
            description: 'Review prompt details, ratings, and sample outputs',
            icon: FileText,
        },
        {
            step: 3,
            title: 'Purchase & Download',
            description: 'Secure checkout with instant access to your prompts',
            icon: Check,
        },
    ];

    return (
        <>
            <Head title="CognitPro - AI Prompt Marketplace">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700"
                    rel="stylesheet"
                />
            </Head>

            <div className="flex min-h-screen flex-col bg-background">
                {/* Navigation */}
                <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                    <div className="container mx-auto flex h-16 items-center justify-between px-4">
                        <Link href="/" className="flex items-center gap-2">
                            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                <Sparkles className="h-5 w-5" />
                            </div>
                            <span className="text-xl font-bold">CognitPro</span>
                        </Link>

                        <nav className="hidden items-center gap-6 md:flex">
                            <Link
                                href="#features"
                                className="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                Features
                            </Link>
                            <Link
                                href="#how-it-works"
                                className="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                How it Works
                            </Link>
                            <Link
                                href="#pricing"
                                className="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >
                                Pricing
                            </Link>
                        </nav>

                        <div className="flex items-center gap-3">
                            {/* Cart icon is shared by authenticated users and guests */}
                            <Button variant="outline" size="icon" asChild className="relative">
                                <Link href="/cart" aria-label="Shopping Cart">
                                    <ShoppingCart className="h-4 w-4" />
                                    {cartCount ? (
                                        <span className="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
                                            {cartCount}
                                        </span>
                                    ) : null}
                                </Link>
                            </Button>
                            {auth?.user ? (
                                <Button asChild>
                                    <Link href={dashboard()}>Dashboard</Link>
                                </Button>
                            ) : (
                                <>
                                    <Button variant="ghost" asChild>
                                        <Link href={login()}>Log in</Link>
                                    </Button>
                                    {canRegister && (
                                        <Button asChild>
                                            <Link href={register()}>
                                                Get Started
                                            </Link>
                                        </Button>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </header>

                {/* Hero Section */}
                <section className="relative overflow-hidden">
                    {/* Background gradient */}
                    <div className="absolute inset-0 -z-10">
                        <div className="absolute inset-0 bg-gradient-to-br from-primary/5 via-background to-chart-5/5" />
                        <div className="absolute top-0 left-1/2 h-[800px] w-[800px] -translate-x-1/2 rounded-full bg-primary/10 opacity-20 blur-3xl" />
                    </div>

                    <div className="container mx-auto px-4 py-20 md:py-32">
                        <div className="mx-auto max-w-4xl text-center">
                            <Badge
                                variant="secondary"
                                className="mb-6 px-4 py-1.5 text-sm"
                            >
                                <Sparkles className="mr-2 h-3.5 w-3.5" />
                                The #1 AI Prompt Marketplace
                            </Badge>

                            <h1 className="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl lg:text-7xl">
                                Discover Premium{' '}
                                <span className="bg-gradient-to-r from-primary to-chart-5 bg-clip-text text-transparent">
                                    AI Prompts
                                </span>
                            </h1>

                            <p className="mx-auto mt-6 max-w-2xl text-lg text-muted-foreground md:text-xl">
                                Buy and sell high-quality prompts for ChatGPT,
                                Midjourney, DALL-E, and more. Join thousands of
                                creators monetizing their prompt engineering
                                expertise.
                            </p>

                            <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Button
                                    size="lg"
                                    className="h-12 px-8 text-base"
                                    asChild
                                >
                                    <Link href="/marketplace">
                                        Browse Prompts
                                        <ArrowRight className="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    size="lg"
                                    variant="outline"
                                    className="h-12 px-8 text-base"
                                    asChild
                                >
                                    <Link
                                        href={
                                            canRegister ? register() : login()
                                        }
                                    >
                                        Start Selling
                                    </Link>
                                </Button>
                            </div>

                            {/* Stats */}
                            <div className="mx-auto mt-16 grid max-w-2xl grid-cols-3 gap-8">
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-foreground">
                                        {stats.prompts.toLocaleString()}+
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        Prompts
                                    </div>
                                </div>
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-foreground">
                                        {stats.sellers.toLocaleString()}+
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        Sellers
                                    </div>
                                </div>
                                <div className="text-center">
                                    <div className="text-3xl font-bold text-foreground">
                                        {stats.sales.toLocaleString()}+
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        Sales
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* AI Models Supported */}
                <section className="border-y bg-muted/30 py-12">
                    <div className="container mx-auto px-4">
                        <p className="mb-8 text-center text-sm font-medium text-muted-foreground">
                            PROMPTS FOR ALL MAJOR AI PLATFORMS
                        </p>
                        <div className="flex flex-wrap items-center justify-center gap-8 md:gap-12">
                            {aiModels.map((model) => (
                                <div
                                    key={model.name}
                                    className="flex items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
                                >
                                    <model.icon
                                        className={`h-5 w-5 ${model.color}`}
                                    />
                                    <span className="font-medium">
                                        {model.name}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section id="features" className="py-20 md:py-28">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto mb-16 max-w-2xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                Why Choose CognitPro?
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                The most trusted marketplace for AI prompts,
                                built for creators and buyers alike.
                            </p>
                        </div>

                        <div className="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                            {features.map((feature) => (
                                <Card
                                    key={feature.title}
                                    className="relative overflow-hidden border-0 shadow-lg transition-shadow hover:shadow-xl"
                                >
                                    <CardContent className="p-6">
                                        <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
                                            <feature.icon className="h-6 w-6 text-primary" />
                                        </div>
                                        <h3 className="text-lg font-semibold">
                                            {feature.title}
                                        </h3>
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            {feature.description}
                                        </p>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* How It Works */}
                <section
                    id="how-it-works"
                    className="bg-muted/30 py-20 md:py-28"
                >
                    <div className="container mx-auto px-4">
                        <div className="mx-auto mb-16 max-w-2xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                How It Works
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                Get started in minutes with our simple 3-step
                                process.
                            </p>
                        </div>

                        <div className="mx-auto grid max-w-5xl gap-8 md:grid-cols-3">
                            {howItWorks.map((item) => (
                                <div
                                    key={item.step}
                                    className="relative text-center"
                                >
                                    <div className="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground">
                                        {item.step}
                                    </div>
                                    <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-background shadow-sm">
                                        <item.icon className="h-6 w-6 text-primary" />
                                    </div>
                                    <h3 className="text-lg font-semibold">
                                        {item.title}
                                    </h3>
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {item.description}
                                    </p>
                                    {item.step < 3 && (
                                        <ChevronRight className="absolute top-16 -right-4 hidden h-8 w-8 text-muted-foreground/30 md:block" />
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Categories Section */}
                <section className="py-20 md:py-28">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto mb-16 max-w-2xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                Explore by Category
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                Find the perfect prompt for your specific use
                                case.
                            </p>
                        </div>

                        <div className="mx-auto grid max-w-5xl gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            {[
                                {
                                    name: 'Writing & Content',
                                    icon: FileText,
                                    count: 1250,
                                },
                                {
                                    name: 'Code & Development',
                                    icon: Code,
                                    count: 890,
                                },
                                {
                                    name: 'Image Generation',
                                    icon: Palette,
                                    count: 2100,
                                },
                                {
                                    name: 'Business & Marketing',
                                    icon: TrendingUp,
                                    count: 750,
                                },
                                {
                                    name: 'Chatbots & Assistants',
                                    icon: MessageSquare,
                                    count: 540,
                                },
                                {
                                    name: 'Data Analysis',
                                    icon: Search,
                                    count: 320,
                                },
                                {
                                    name: 'Education & Learning',
                                    icon: FileText,
                                    count: 480,
                                },
                                {
                                    name: 'Creative Writing',
                                    icon: Sparkles,
                                    count: 920,
                                },
                            ].map((category) => (
                                <Link
                                    key={category.name}
                                    href={`/marketplace?category=${encodeURIComponent(category.name)}`}
                                    className="group flex items-center gap-4 rounded-xl border bg-card p-4 transition-colors hover:bg-accent"
                                >
                                    <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                        <category.icon className="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <div className="font-medium transition-colors group-hover:text-primary">
                                            {category.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {category.count} prompts
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>

                        <div className="mt-10 text-center">
                            <Button variant="outline" size="lg" asChild>
                                <Link href="/marketplace">
                                    View All Categories
                                    <ArrowRight className="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>

                {/* Testimonials */}
                <section className="bg-muted/30 py-20 md:py-28">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto mb-16 max-w-2xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                Loved by Creators
                            </h2>
                            <p className="mt-4 text-lg text-muted-foreground">
                                See what our community has to say about
                                CognitPro.
                            </p>
                        </div>

                        <div className="mx-auto grid max-w-6xl gap-6 md:grid-cols-3">
                            {[
                                {
                                    quote: "CognitPro has completely transformed how I approach prompt engineering. I've made over $5,000 in just 3 months selling my best prompts.",
                                    author: 'Sarah Chen',
                                    role: 'AI Artist & Prompt Engineer',
                                    rating: 5,
                                },
                                {
                                    quote: "The quality of prompts here is unmatched. I've saved countless hours by purchasing proven prompts instead of starting from scratch.",
                                    author: 'Marcus Johnson',
                                    role: 'Content Creator',
                                    rating: 5,
                                },
                                {
                                    quote: 'As a developer, finding code-related prompts that actually work was a game-changer. The verification system ensures quality.',
                                    author: 'Emily Rodriguez',
                                    role: 'Software Engineer',
                                    rating: 5,
                                },
                            ].map((testimonial, i) => (
                                <Card key={i} className="border-0 shadow-lg">
                                    <CardContent className="p-6">
                                        <div className="mb-4 flex gap-1">
                                            {[...Array(testimonial.rating)].map(
                                                (_, j) => (
                                                    <Star
                                                        key={j}
                                                        className="h-4 w-4 fill-warning text-warning"
                                                    />
                                                ),
                                            )}
                                        </div>
                                        <p className="mb-4 text-sm text-muted-foreground">
                                            "{testimonial.quote}"
                                        </p>
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary">
                                                {testimonial.author.charAt(0)}
                                            </div>
                                            <div>
                                                <div className="text-sm font-medium">
                                                    {testimonial.author}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {testimonial.role}
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="py-20 md:py-28">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto max-w-4xl rounded-2xl bg-gradient-to-r from-primary to-chart-5 p-8 text-center text-white md:p-16">
                            <h2 className="text-3xl font-bold tracking-tight sm:text-4xl">
                                Ready to Start?
                            </h2>
                            <p className="mx-auto mt-4 max-w-xl text-lg text-white/90">
                                Join thousands of creators and buyers on the
                                world's leading AI prompt marketplace.
                            </p>
                            <div className="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                                <Button
                                    size="lg"
                                    variant="secondary"
                                    className="h-12 px-8 text-base"
                                    asChild
                                >
                                    <Link
                                        href={
                                            canRegister ? register() : login()
                                        }
                                    >
                                        Create Free Account
                                    </Link>
                                </Button>
                                <Button
                                    size="lg"
                                    variant="outline"
                                    className="h-12 border-white/30 bg-transparent px-8 text-base text-white hover:bg-white/10 hover:text-white"
                                    asChild
                                >
                                    <Link href="/marketplace">
                                        Browse Prompts
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t bg-muted/30 py-12">
                    <div className="container mx-auto px-4">
                        <div className="grid gap-8 md:grid-cols-4">
                            <div>
                                <Link
                                    href="/"
                                    className="mb-4 flex items-center gap-2"
                                >
                                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                        <Sparkles className="h-5 w-5" />
                                    </div>
                                    <span className="text-xl font-bold">
                                        CognitPro
                                    </span>
                                </Link>
                                <p className="text-sm text-muted-foreground">
                                    The world's leading marketplace for AI
                                    prompts.
                                </p>
                            </div>

                            <div>
                                <h4 className="mb-4 font-semibold">
                                    Marketplace
                                </h4>
                                <ul className="space-y-2 text-sm text-muted-foreground">
                                    <li>
                                        <Link
                                            href="/marketplace"
                                            className="transition-colors hover:text-foreground"
                                        >
                                            Browse Prompts
                                        </Link>
                                    </li>
                                    <li>
                                        <Link
                                            href="/marketplace?sort_by=popular"
                                            className="transition-colors hover:text-foreground"
                                        >
                                            Popular
                                        </Link>
                                    </li>
                                    <li>
                                        <Link
                                            href="/marketplace?sort_by=created_at"
                                            className="transition-colors hover:text-foreground"
                                        >
                                            New Arrivals
                                        </Link>
                                    </li>
                                    <li>
                                        <Link
                                            href="/marketplace?featured=1"
                                            className="transition-colors hover:text-foreground"
                                        >
                                            Featured
                                        </Link>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h4 className="mb-4 font-semibold">Sellers</h4>
                                <ul className="space-y-2 text-sm text-muted-foreground">
                                    <li>
                                        <Link
                                            href={register()}
                                            className="transition-colors hover:text-foreground"
                                        >
                                            Become a Seller
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div className="mt-12 flex flex-col items-center justify-between gap-4 border-t pt-8 md:flex-row">
                            <p className="text-sm text-muted-foreground">
                                © {new Date().getFullYear()} CognitPro. All
                                rights reserved.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
