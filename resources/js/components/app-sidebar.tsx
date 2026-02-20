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
    ShoppingCart,
    Package,
    PlusCircle,
    TrendingUp,
    Users,
    Shield,
    FileText,
    Settings,
} from 'lucide-react';
import AppLogo from './app-logo';

// Common navigation items for all users
const commonNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

// Navigation items for buyers
const buyerNavItems: NavItem[] = [
    {
        title: 'My Purchases',
        href: '/purchases',
        icon: Package,
    },
    {
        title: 'Cart',
        href: '/cart',
        icon: ShoppingCart,
    },
];

// Navigation items for sellers
const sellerNavItems: NavItem[] = [
    {
        title: 'Sales',
        href: '/seller/sales',
        icon: TrendingUp,
    },
    {
        title: 'Create Prompt',
        href: '/prompts/create',
        icon: PlusCircle,
    },
];

// Navigation items for admins
const adminNavItems: NavItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin',
        icon: Shield,
    },
    {
        title: 'User Management',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Prompt Moderation',
        href: '/admin/prompts',
        icon: FileText,
    },
];

// Settings items for all authenticated users
const settingsNavItems: NavItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
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
    const user = auth?.user;

    // Build navigation items based on user role
    const getNavItems = (): NavItem[] => {
        if (!user) {
            return commonNavItems;
        }

        const items: NavItem[] = [...commonNavItems];

        switch (user.role) {
            case 'admin':
                // Admins see admin items + all marketplace features
                items.push(...adminNavItems);
                items.push({
                    title: 'My Purchases',
                    href: '/purchases',
                    icon: Package,
                });
                items.push({
                    title: 'Cart',
                    href: '/cart',
                    icon: ShoppingCart,
                });
                items.push({
                    title: 'Sales',
                    href: '/seller/sales',
                    icon: TrendingUp,
                });
                items.push({
                    title: 'Create Prompt',
                    href: '/prompts/create',
                    icon: PlusCircle,
                });
                break;

            case 'seller':
                // Sellers see seller items + buyer items
                items.push(...sellerNavItems);
                items.push({
                    title: 'My Purchases',
                    href: '/purchases',
                    icon: Package,
                });
                items.push({
                    title: 'Cart',
                    href: '/cart',
                    icon: ShoppingCart,
                });
                break;

            case 'buyer':
            default:
                // Buyers see buyer items
                items.push(...buyerNavItems);
                break;
        }

        // Add settings for all authenticated users
        items.push(...settingsNavItems);

        return items;
    };

    const mainNavItems = getNavItems();

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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
