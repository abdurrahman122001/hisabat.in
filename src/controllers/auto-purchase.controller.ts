interface AutoPurchaseProduct {
    thresholdAmount: string;
    product: {
        product_id: number;
        stock_quantity: number;
    };
}

interface AutoPurchaseRequest {
    memberID: number;
    autoPurchase: boolean;
    products: AutoPurchaseProduct[];
    paymentMethod: string;
    shippingAddress: number;
}

export const registerAutoPurchase = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const {
            memberID,
            autoPurchase,
            products,
            paymentMethod,
            shippingAddress,
        }: AutoPurchaseRequest = req.body;

        logger.info({
            message: 'Starting auto purchase registration process',
            description: {
                action: 'Auto Purchase Registration',
                details: {
                    memberId: memberID,
                    autoPurchase,
                    productCount: products.length,
                    timestamp: new Date().toISOString()
                }
            }
        });

        await memberRepo.update({
            auto_purchase: autoPurchase
        }, {
            where: {
                member_id: memberID
            }
        });

        const purchase = products.map((product: AutoPurchaseProduct) => ({
            member_id: memberID,
            is_enabled: autoPurchase,
            threshold_amount: Number(product.thresholdAmount),
            product_id: product.product.product_id,
            quantity: product.product.stock_quantity,
            payment_method: paymentMethod,
            shopping_address_id: Number(shippingAddress)
        }));
        
        await purchaseRepo.bulkCreate(purchase);

        return returnSuccess(
            res,
            "Auto purchase registered successfully",
            {
                action: 'Auto Purchase Registration',
                details: {
                    memberId: memberID,
                    autoPurchase,
                    productCount: products.length,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Auto purchase settings updated successfully'
                }
            },
            'Auto purchase registered successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to register auto purchase',
                description: {
                    action: 'Auto Purchase Registration Error',
                    details: {
                        memberId: req.body.memberID,
                        error: e.message,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
}; 