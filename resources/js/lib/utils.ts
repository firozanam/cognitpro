import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * Safely converts a value to a number and formats it with toFixed.
 * Handles null, undefined, and string values gracefully.
 */
export function safeToFixed(value: any, decimals: number = 2): string {
    if (value === null || value === undefined) {
        return '0.' + '0'.repeat(decimals);
    }

    const num = parseFloat(String(value));
    if (isNaN(num)) {
        return '0.' + '0'.repeat(decimals);
    }

    return num.toFixed(decimals);
}

/**
 * Safely formats a price value with proper handling of different price types.
 */
export function formatPrice(price: any, priceType: string, minimumPrice?: any): string {
    if (priceType === 'free') {
        return 'Free';
    }

    if (priceType === 'pay_what_you_want') {
        return `$${safeToFixed(minimumPrice)}+`;
    }

    return `$${safeToFixed(price)}`;
}

/**
 * Safely formats a rating value.
 */
export function formatRating(rating: any, decimals: number = 1): string {
    return safeToFixed(rating, decimals);
}

/**
 * Safely converts a value to a number, returning 0 for invalid values.
 */
export function safeNumber(value: any): number {
    if (value === null || value === undefined) {
        return 0;
    }

    const num = parseFloat(String(value));
    return isNaN(num) ? 0 : num;
}
