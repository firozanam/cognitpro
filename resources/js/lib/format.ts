import type { Prompt } from '@/types/prompt';

/**
 * Shared formatting helpers — one implementation instead of one
 * per page (STD-09).
 */

export function formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(amount);
}

export function formatDate(
    date: string | null | undefined,
    options: { month?: 'long' | 'short'; withTime?: boolean } = {},
): string {
    if (!date) {
        return 'Pending';
    }

    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: options.month ?? 'long',
        day: 'numeric',
        ...(options.withTime === true
            ? { hour: '2-digit', minute: '2-digit' as const }
            : {}),
    });
}

/**
 * Price display honoring the pricing model: fixed shows the amount,
 * free and pay-what-you-want show their labels.
 */
export function formatPromptPrice(
    prompt: Pick<Prompt, 'pricing_model' | 'price'>,
): string {
    if (prompt.pricing_model === 'free') {
        return 'Free';
    }

    if (prompt.pricing_model === 'pay_what_you_want') {
        return 'Pay what you want';
    }

    return formatCurrency(Number(prompt.price));
}
