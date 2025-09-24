import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    FileText,
    Plus,
    BarChart3,
    DollarSign,
    CreditCard,
    MessageSquare,
    ShoppingBag,
    Star,
    Heart,
    Grid3X3
} from 'lucide-react';
import AppLogo from './app-logo';

// Seller navigation items
const getSellerNavItems = (): NavItem[] => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'My Prompts',
        href: '/dashboard/prompts',
        icon: FileText,
    },
    {
        title: 'Create Prompt',
        href: '/prompts/create',
        icon: Plus,
    },
    {
        title: 'Analytics',
        href: '/dashboard/analytics',
        icon: BarChart3,
    },
    {
        title: 'Sales & Revenue',
        href: '/dashboard/sales',
        icon: DollarSign,
    },
    {
        title: 'Payouts',
        href: '/dashboard/payouts',
        icon: CreditCard,
    },
    {
        title: 'Reviews',
        href: '/dashboard/reviews',
        icon: MessageSquare,
    },
];

// Buyer navigation items
const getBuyerNavItems = (): NavItem[] => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Browse Prompts',
        href: '/prompts',
        icon: ShoppingBag,
    },
    {
        title: 'My Purchases',
        href: '/dashboard/purchases',
        icon: ShoppingBag,
    },
    {
        title: 'My Reviews',
        href: '/dashboard/reviews',
        icon: Star,
    },
    {
        title: 'Favorites',
        href: '/dashboard/favorites',
        icon: Heart,
    },
    {
        title: 'Categories',
        href: '/categories',
        icon: Grid3X3,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;

    // Determine navigation items based on user role
    const getNavigationItems = (): NavItem[] => {
        if (!auth.user) {
            return [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
            ];
        }

        // Sellers can also buy, so they get both seller and some buyer functionality
        if (auth.user.role === 'seller') {
            return getSellerNavItems();
        }

        // Buyers get buyer-specific navigation
        if (auth.user.role === 'buyer') {
            return getBuyerNavItems();
        }

        // Default fallback (shouldn't happen for admin as they have separate app)
        return [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
        ];
    };

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={getNavigationItems()} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
