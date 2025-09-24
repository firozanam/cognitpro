import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import '@testing-library/jest-dom';
import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';
import { StripePayment } from '../../components/stripe-payment';

// Mock Stripe
const mockStripe = {
    elements: jest.fn(() => ({
        create: jest.fn(() => ({
            mount: jest.fn(),
            unmount: jest.fn(),
            on: jest.fn(),
            off: jest.fn(),
        })),
        getElement: jest.fn(),
    })),
    createPaymentMethod: jest.fn(),
    confirmCardPayment: jest.fn(),
};

jest.mock('@stripe/stripe-js', () => ({
    loadStripe: jest.fn(() => Promise.resolve(mockStripe)),
}));

jest.mock('@stripe/react-stripe-js', () => ({
    Elements: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
    useStripe: () => mockStripe,
    useElements: () => ({
        getElement: jest.fn(() => ({
            // Mock card element
        })),
    }),
    CardElement: () => <div data-testid="card-element">Card Element</div>,
}));

// Mock fetch
global.fetch = jest.fn();

const mockPrompt = {
    id: 1,
    uuid: 'test-uuid-123',
    title: 'Test AI Prompt',
    price: 9.99,
    price_type: 'fixed' as const,
};

const mockUser = {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
};

describe('StripePayment', () => {
    const defaultProps = {
        prompt: mockPrompt,
        user: mockUser,
        amount: 9.99,
        onSuccess: jest.fn(),
        onError: jest.fn(),
    };

    beforeEach(() => {
        jest.clearAllMocks();
        (global.fetch as jest.Mock).mockClear();
    });

    it('renders the payment form', () => {
        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        expect(screen.getByTestId('card-element')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /pay/i })).toBeInTheDocument();
    });

    it('displays the correct amount', () => {
        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        expect(screen.getByText('$9.99')).toBeInTheDocument();
    });

    it('shows loading state when processing payment', async () => {
        // Mock successful payment intent creation
        (global.fetch as jest.Mock).mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                payment_intent_id: 'pi_test_123',
                client_secret: 'pi_test_123_secret',
            }),
        });

        // Mock successful payment confirmation
        mockStripe.confirmCardPayment.mockResolvedValueOnce({
            paymentIntent: {
                status: 'succeeded',
                id: 'pi_test_123',
            },
        });

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        // Should show loading state
        await waitFor(() => {
            expect(screen.getByText(/processing/i)).toBeInTheDocument();
        });
    });

    it('handles successful payment', async () => {
        // Mock successful payment intent creation
        (global.fetch as jest.Mock)
            .mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    payment_intent_id: 'pi_test_123',
                    client_secret: 'pi_test_123_secret',
                }),
            })
            .mockResolvedValueOnce({
                ok: true,
                json: async () => ({
                    success: true,
                    purchase: { id: 1 },
                }),
            });

        // Mock successful payment confirmation
        mockStripe.confirmCardPayment.mockResolvedValueOnce({
            paymentIntent: {
                status: 'succeeded',
                id: 'pi_test_123',
            },
        });

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        await waitFor(() => {
            expect(defaultProps.onSuccess).toHaveBeenCalled();
        });
    });

    it('handles payment failure', async () => {
        // Mock successful payment intent creation
        (global.fetch as jest.Mock).mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                payment_intent_id: 'pi_test_123',
                client_secret: 'pi_test_123_secret',
            }),
        });

        // Mock failed payment confirmation
        mockStripe.confirmCardPayment.mockResolvedValueOnce({
            error: {
                message: 'Your card was declined.',
                type: 'card_error',
            },
        });

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        await waitFor(() => {
            expect(defaultProps.onError).toHaveBeenCalledWith('Your card was declined.');
        });
    });

    it('handles payment intent creation failure', async () => {
        // Mock failed payment intent creation
        (global.fetch as jest.Mock).mockResolvedValueOnce({
            ok: false,
            json: async () => ({
                error: 'Failed to create payment intent',
            }),
        });

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        await waitFor(() => {
            expect(defaultProps.onError).toHaveBeenCalledWith('Failed to create payment intent');
        });
    });

    it('handles network errors', async () => {
        // Mock network error
        (global.fetch as jest.Mock).mockRejectedValueOnce(new Error('Network error'));

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        await waitFor(() => {
            expect(defaultProps.onError).toHaveBeenCalledWith('Network error occurred. Please try again.');
        });
    });

    it('disables pay button when processing', async () => {
        // Mock slow payment intent creation
        (global.fetch as jest.Mock).mockImplementationOnce(
            () => new Promise(resolve => setTimeout(() => resolve({
                ok: true,
                json: async () => ({
                    payment_intent_id: 'pi_test_123',
                    client_secret: 'pi_test_123_secret',
                }),
            }), 100))
        );

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        // Button should be disabled during processing
        expect(payButton).toBeDisabled();
    });

    it('shows payment requires authentication', async () => {
        // Mock payment intent creation
        (global.fetch as jest.Mock).mockResolvedValueOnce({
            ok: true,
            json: async () => ({
                payment_intent_id: 'pi_test_123',
                client_secret: 'pi_test_123_secret',
            }),
        });

        // Mock payment requiring authentication
        mockStripe.confirmCardPayment.mockResolvedValueOnce({
            paymentIntent: {
                status: 'requires_action',
                id: 'pi_test_123',
            },
        });

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        const payButton = screen.getByRole('button', { name: /pay/i });
        fireEvent.click(payButton);
        
        await waitFor(() => {
            expect(screen.getByText(/additional authentication required/i)).toBeInTheDocument();
        });
    });

    it('handles pay what you want pricing', () => {
        const pwywProps = {
            ...defaultProps,
            prompt: {
                ...mockPrompt,
                price_type: 'pay_what_you_want' as const,
                minimum_price: 1.00,
            },
            amount: 15.00, // User chose to pay more
        };

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...pwywProps} />
            </Elements>
        );
        
        expect(screen.getByText('$15.00')).toBeInTheDocument();
    });

    it('validates minimum amount for pay what you want', () => {
        const pwywProps = {
            ...defaultProps,
            prompt: {
                ...mockPrompt,
                price_type: 'pay_what_you_want' as const,
                minimum_price: 5.00,
            },
            amount: 3.00, // Below minimum
        };

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...pwywProps} />
            </Elements>
        );
        
        expect(screen.getByText(/minimum amount is \$5\.00/i)).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /pay/i })).toBeDisabled();
    });

    it('shows secure payment notice', () => {
        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...defaultProps} />
            </Elements>
        );
        
        expect(screen.getByText(/secured by stripe/i)).toBeInTheDocument();
    });

    it('formats currency correctly', () => {
        const highAmountProps = {
            ...defaultProps,
            amount: 1234.56,
        };

        render(
            <Elements stripe={mockStripe as any}>
                <StripePayment {...highAmountProps} />
            </Elements>
        );
        
        expect(screen.getByText('$1,234.56')).toBeInTheDocument();
    });
});
