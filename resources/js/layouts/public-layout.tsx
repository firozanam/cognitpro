import { FlashToasts } from '@/components/flash-toasts';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Menu, ShoppingCart, Sparkles, X } from 'lucide-react';
import { useState, type ReactNode } from 'react';

interface PublicNavLink {
    label: string;
    href: string;
    active?: boolean;
}

const defaultNavLinks: PublicNavLink[] = [
    { label: 'Marketplace', href: '/marketplace' },
];

// Public header for pages reachable by unauthenticated users
function PublicHeader({ navLinks = defaultNavLinks }: { navLinks?: PublicNavLink[] }) {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { cartCount } = usePage<SharedData>().props;

    return (
        <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto flex h-16 items-center justify-between px-4">
                <Link href="/" className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <Sparkles className="h-5 w-5" />
                    </div>
                    <span className="text-xl font-bold">CognitPro</span>
                </Link>

                {/* Desktop Navigation */}
                <nav className="hidden items-center gap-6 md:flex">
                    {navLinks.map((link) => (
                        <Link
                            key={link.href}
                            href={link.href}
                            className={`text-sm font-medium ${link.active ? 'text-foreground' : 'text-muted-foreground transition-colors hover:text-foreground'}`}
                        >
                            {link.label}
                        </Link>
                    ))}
                </nav>

                {/* Guests see the cart too; only checkout requires login */}
                <div className="flex items-center gap-2">
                    <Link href="/cart" aria-label="Shopping Cart">
                        <Button variant="outline" size="icon" className="relative">
                            <ShoppingCart className="h-4 w-4" />
                            {cartCount ? (
                                <span className="absolute -top-1.5 -right-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-medium text-primary-foreground">
                                    {cartCount}
                                </span>
                            ) : null}
                        </Button>
                    </Link>

                    <div className="hidden items-center gap-3 md:flex">
                        <Button variant="ghost" asChild>
                            <Link href="/login">Log in</Link>
                        </Button>
                        <Button asChild>
                            <Link href="/register">Get Started</Link>
                        </Button>
                    </div>

                    {/* Mobile menu button */}
                    <Button
                        variant="ghost"
                        size="icon"
                        className="md:hidden"
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                    >
                        {mobileMenuOpen ? (
                            <X className="h-5 w-5" />
                        ) : (
                            <Menu className="h-5 w-5" />
                        )}
                    </Button>
                </div>
            </div>

            {/* Mobile Navigation */}
            {mobileMenuOpen && (
                <div className="border-t bg-background md:hidden">
                    <nav className="container mx-auto flex flex-col gap-4 px-4 py-4">
                        {navLinks.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className={`text-sm font-medium ${link.active ? 'text-foreground' : 'text-muted-foreground'}`}
                            >
                                {link.label}
                            </Link>
                        ))}
                        <div className="flex flex-col gap-2 border-t pt-4">
                            <Button
                                variant="ghost"
                                asChild
                                className="justify-start"
                            >
                                <Link href="/login">Log in</Link>
                            </Button>
                            <Button asChild className="justify-start">
                                <Link href="/register">Get Started</Link>
                            </Button>
                        </div>
                    </nav>
                </div>
            )}
        </header>
    );
}

// Public footer component
function PublicFooter() {
    return (
        <footer className="mt-auto border-t bg-muted/30 py-8">
            <div className="container mx-auto px-4">
                <div className="flex flex-col items-center justify-between gap-4 md:flex-row">
                    <div className="flex items-center gap-2">
                        <div className="flex h-6 w-6 items-center justify-center rounded bg-primary text-primary-foreground">
                            <Sparkles className="h-4 w-4" />
                        </div>
                        <span className="font-semibold">CognitPro</span>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        © {new Date().getFullYear()} CognitPro. All rights
                        reserved.
                    </p>
                </div>
            </div>
        </footer>
    );
}

interface PublicLayoutProps {
    children: ReactNode;
    navLinks?: PublicNavLink[];
}

export default function PublicLayout({
    children,
    navLinks,
}: PublicLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col bg-background">
            <FlashToasts />
            <PublicHeader navLinks={navLinks} />
            <main className="flex-1">{children}</main>
            <PublicFooter />
        </div>
    );
}
