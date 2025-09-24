import React, { useState } from 'react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Star, User, Calendar, DollarSign, Shield, CreditCard } from 'lucide-react';
import StripePayment from './stripe-payment';
import { formatPrice, safeToFixed } from '@/lib/utils';

interface Prompt {
  id: number;
  uuid: string;
  title: string;
  description: string;
  price: number;
  price_type: 'fixed' | 'pay_what_you_want' | 'free';
  minimum_price?: number;
  category: {
    name: string;
    color: string;
  };
  user: {
    name: string;
  };
  tags: Array<{
    name: string;
  }>;
  rating: number;
  review_count: number;
  created_at: string;
}

interface PurchaseModalProps {
  prompt: Prompt;
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: (purchaseId: string) => void;
}

const PurchaseModal: React.FC<PurchaseModalProps> = ({
  prompt,
  isOpen,
  onClose,
  onSuccess
}) => {
  const [showPayment, setShowPayment] = useState(false);
  const [customAmount, setCustomAmount] = useState(prompt.price);

  const handleProceedToPayment = () => {
    setShowPayment(true);
  };

  const handlePaymentSuccess = (purchaseId: string) => {
    if (onSuccess) {
      onSuccess(purchaseId);
    }
    onClose();
  };

  const handlePaymentCancel = () => {
    setShowPayment(false);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const getEffectivePrice = () => {
    if (prompt.price_type === 'free') return 0;
    if (prompt.price_type === 'pay_what_you_want') return customAmount;
    return prompt.price;
  };

  if (showPayment) {
    return (
      <Dialog open={isOpen} onOpenChange={onClose}>
        <DialogContent className="max-w-lg">
          <DialogHeader>
            <DialogTitle>Complete Purchase</DialogTitle>
            <DialogDescription>
              Secure payment powered by Stripe
            </DialogDescription>
          </DialogHeader>
          
          <StripePayment
            promptId={prompt.id}
            amount={getEffectivePrice()}
            promptTitle={prompt.title}
            onSuccess={handlePaymentSuccess}
            onCancel={handlePaymentCancel}
          />
        </DialogContent>
      </Dialog>
    );
  }

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-xl">Purchase Prompt</DialogTitle>
          <DialogDescription>
            Review the details before completing your purchase
          </DialogDescription>
        </DialogHeader>

        <div className="space-y-6">
          {/* Prompt Details */}
          <div className="space-y-4">
            <div>
              <h3 className="text-lg font-semibold mb-2">{prompt.title}</h3>
              <p className="text-gray-600 text-sm leading-relaxed">
                {prompt.description}
              </p>
            </div>

            {/* Metadata */}
            <div className="flex flex-wrap gap-4 text-sm text-gray-500">
              <div className="flex items-center gap-1">
                <User className="h-4 w-4" />
                <span>by {prompt.user.name}</span>
              </div>
              
              <div className="flex items-center gap-1">
                <Calendar className="h-4 w-4" />
                <span>{formatDate(prompt.created_at)}</span>
              </div>
              
              {prompt.rating > 0 && (
                <div className="flex items-center gap-1">
                  <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                  <span>{prompt.rating.toFixed(1)} ({prompt.review_count} reviews)</span>
                </div>
              )}
            </div>

            {/* Category and Tags */}
            <div className="flex flex-wrap gap-2">
              <Badge 
                variant="secondary" 
                style={{ backgroundColor: prompt.category.color + '20', color: prompt.category.color }}
              >
                {prompt.category.name}
              </Badge>
              
              {prompt.tags.slice(0, 3).map((tag, index) => (
                <Badge key={index} variant="outline" className="text-xs">
                  {tag.name}
                </Badge>
              ))}
              
              {prompt.tags.length > 3 && (
                <Badge variant="outline" className="text-xs">
                  +{prompt.tags.length - 3} more
                </Badge>
              )}
            </div>
          </div>

          <Separator />

          {/* Pricing */}
          <div className="space-y-4">
            <h4 className="font-semibold flex items-center gap-2">
              <DollarSign className="h-4 w-4" />
              Pricing
            </h4>
            
            {prompt.price_type === 'free' ? (
              <div className="text-lg font-semibold text-green-600">
                Free
              </div>
            ) : prompt.price_type === 'fixed' ? (
              <div className="text-lg font-semibold">
                ${safeToFixed(prompt.price)}
              </div>
            ) : (
              <div className="space-y-2">
                <div className="text-sm text-gray-600">
                  Pay what you want (minimum: ${safeToFixed(prompt.minimum_price)})
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-sm">$</span>
                  <input
                    type="number"
                    min={prompt.minimum_price || 0}
                    step="0.01"
                    value={customAmount}
                    onChange={(e) => setCustomAmount(parseFloat(e.target.value) || 0)}
                    className="flex-1 px-3 py-2 border rounded-md text-lg font-semibold"
                  />
                </div>
              </div>
            )}
          </div>

          <Separator />

          {/* Security Notice */}
          <div className="bg-blue-50 p-4 rounded-lg">
            <div className="flex items-start gap-3">
              <Shield className="h-5 w-5 text-blue-600 mt-0.5" />
              <div className="space-y-1">
                <h5 className="font-medium text-blue-900">Secure Purchase</h5>
                <p className="text-sm text-blue-700">
                  Your payment is processed securely through Stripe. We never store your card details.
                  You'll get instant access to the prompt after successful payment.
                </p>
              </div>
            </div>
          </div>

          {/* Purchase Summary */}
          <div className="bg-gray-50 p-4 rounded-lg space-y-2">
            <div className="flex justify-between items-center">
              <span className="font-medium">Total</span>
              <span className="text-lg font-bold">
                ${getEffectivePrice().toFixed(2)}
              </span>
            </div>
            <div className="text-xs text-gray-500">
              One-time purchase • Instant access • No subscription
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex gap-3">
            <Button
              onClick={handleProceedToPayment}
              className="flex-1"
              disabled={prompt.price_type === 'pay_what_you_want' && customAmount < (prompt.minimum_price || 0)}
            >
              <CreditCard className="h-4 w-4 mr-2" />
              {getEffectivePrice() === 0 ? 'Get Free Prompt' : `Purchase for $${safeToFixed(getEffectivePrice())}`}
            </Button>
            
            <Button variant="outline" onClick={onClose}>
              Cancel
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default PurchaseModal;
