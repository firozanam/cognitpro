import { Breadcrumbs } from '@/components/breadcrumbs';
import { Button } from '@/components/ui/button';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { type BreadcrumbItem as BreadcrumbItemType, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ShoppingCart, Store } from 'lucide-react';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { cartCount } = usePage<SharedData>().props;

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>
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
                <Link href="/marketplace">
                    <Button variant="outline" size="sm" className="gap-2">
                        <Store className="h-4 w-4" />
                        Marketplace
                    </Button>
                </Link>
            </div>
        </header>
    );
}
