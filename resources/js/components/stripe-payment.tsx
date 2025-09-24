import React, { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import {
  Elements,
  CardElement,
  useStripe,
  useElements
} from '@stripe/react-stripe-js';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Loader2, CreditCard, CheckCircle } from 'lucide-react';
import { router } from '@inertiajs/react';
import { safeToFixed } from '@/lib/utils';

interface StripePaymentProps {
  promptId: number;
  amount: number;
  promptTitle: string;
  onSuccess?: (purchaseId: string) => void;
  onCancel?: () => void;
}

interface PaymentFormProps {
  promptId: number;
  amount: number;
  promptTitle: string;
  onSuccess?: (purchaseId: string) => void;
  onCancel?: () => void;
}

const PaymentForm: React.FC<PaymentFormProps> = ({
  promptId,
  amount,
  promptTitle,
  onSuccess,
  onCancel
}) => {
  const stripe = useStripe();
  const elements = useElements();
  const [isProcessing, setIsProcessing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);
  const [clientSecret, setClientSecret] = useState<string | null>(null);

  // Create payment intent when component mounts
  useEffect(() => {
    const createPaymentIntent = async () => {
      try {
        const response = await fetch('/api/payments/create-intent', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({
            prompt_id: promptId,
            amount: amount,
          }),
        });

        const data = await response.json();

        if (data.success) {
          setClientSecret(data.data.client_secret);
        } else {
          setError(data.message || 'Failed to create payment intent');
        }
      } catch (err) {
        setError('Failed to initialize payment');
      }
    };

    createPaymentIntent();
  }, [promptId, amount]);

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();

    if (!stripe || !elements || !clientSecret) {
      return;
    }

    setIsProcessing(true);
    setError(null);

    const cardElement = elements.getElement(CardElement);

    if (!cardElement) {
      setError('Card element not found');
      setIsProcessing(false);
      return;
    }

    try {
      // Confirm payment with Stripe
      const { error: stripeError, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
        payment_method: {
          card: cardElement,
        },
      });

      if (stripeError) {
        setError(stripeError.message || 'Payment failed');
        setIsProcessing(false);
        return;
      }

      if (paymentIntent?.status === 'succeeded') {
        // Confirm payment with our backend
        const response = await fetch('/api/payments/confirm', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({
            payment_intent_id: paymentIntent.id,
          }),
        });

        const data = await response.json();

        if (data.success) {
          setSuccess(true);
          if (onSuccess) {
            onSuccess(data.data.purchase_id);
          }
          
          // Redirect to purchased prompt after a short delay
          setTimeout(() => {
            router.visit(`/prompts/${data.data.prompt.uuid}`);
          }, 2000);
        } else {
          setError(data.message || 'Failed to confirm payment');
        }
      }
    } catch (err) {
      setError('Payment processing failed');
    } finally {
      setIsProcessing(false);
    }
  };

  if (success) {
    return (
      <Card className="w-full max-w-md mx-auto">
        <CardContent className="pt-6">
          <div className="text-center">
            <CheckCircle className="h-16 w-16 text-green-500 mx-auto mb-4" />
            <h3 className="text-lg font-semibold text-green-700 mb-2">Payment Successful!</h3>
            <p className="text-gray-600 mb-4">
              You now have access to "{promptTitle}". Redirecting...
            </p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="w-full max-w-md mx-auto">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <CreditCard className="h-5 w-5" />
          Complete Purchase
        </CardTitle>
        <CardDescription>
          Purchase "{promptTitle}" for ${safeToFixed(amount)}
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}

          <div className="space-y-2">
            <label className="text-sm font-medium">Card Details</label>
            <div className="p-3 border rounded-md">
              <CardElement
                options={{
                  style: {
                    base: {
                      fontSize: '16px',
                      color: '#424770',
                      '::placeholder': {
                        color: '#aab7c4',
                      },
                    },
                  },
                }}
              />
            </div>
          </div>

          <div className="flex gap-2">
            <Button
              type="submit"
              disabled={!stripe || isProcessing || !clientSecret}
              className="flex-1"
            >
              {isProcessing ? (
                <>
                  <Loader2 className="h-4 w-4 animate-spin mr-2" />
                  Processing...
                </>
              ) : (
                `Pay $${safeToFixed(amount)}`
              )}
            </Button>
            
            {onCancel && (
              <Button
                type="button"
                variant="outline"
                onClick={onCancel}
                disabled={isProcessing}
              >
                Cancel
              </Button>
            )}
          </div>
        </form>
      </CardContent>
    </Card>
  );
};

const StripePayment: React.FC<StripePaymentProps> = (props) => {
  const [stripePromise, setStripePromise] = useState<Promise<any> | null>(null);

  useEffect(() => {
    // In a real implementation, this would come from your backend
    const publishableKey = 'pk_test_mock_key'; // This should be fetched from your backend
    setStripePromise(loadStripe(publishableKey));
  }, []);

  if (!stripePromise) {
    return (
      <Card className="w-full max-w-md mx-auto">
        <CardContent className="pt-6">
          <div className="text-center">
            <Loader2 className="h-8 w-8 animate-spin mx-auto mb-4" />
            <p>Loading payment form...</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Elements stripe={stripePromise}>
      <PaymentForm {...props} />
    </Elements>
  );
};

export default StripePayment;
