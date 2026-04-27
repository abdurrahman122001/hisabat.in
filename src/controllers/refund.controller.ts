export const registerRefund = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { orderId } = req.params;
        const { reason, refundAmount, total } = req.body;

        const order = await orderRepo.findOne({
            attributes: {
                exclude: ['created_at', 'updated_at'],
            },
            include: [
                {
                    model: OrderItem,
                    as: 'order_items',
                    attributes: {
                        exclude: ['created_at', 'updated_at'],
                    },
                },
            ],
            where: {
                order_id: parseInt(orderId as string),
            },
        });

        if (!order) {
            return returnError(
                res,
                {
                    action: 'Register Refund',
                    details: {
                        orderId: parseInt(orderId as string),
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Order not found'
                    }
                },
                'Order not found',
                404
            );
        }

        let refundData: any = {
            order_id: parseInt(orderId as string),
            member_id: order.member_id,
            refund_amount: refundAmount,
            refund_date: new Date(),
            reason: reason || null,
            created_at: new Date(),
            total: total || null
        };

        const refund = await refundRepo.create(refundData);

        return returnSuccess(
            res,
            { refundId: refund.refund_id },
            {
                action: 'Register Refund',
                details: {
                    refundId: refund.refund_id,
                    orderId: parseInt(orderId as string),
                    memberId: order.member_id,
                    refund: {
                        amount: refundAmount,
                        reason,
                        total,
                        date: refundData.refund_date
                    },
                    order: {
                        id: order.order_id,
                        items: order.order_items?.map(item => ({
                            id: item.order_item_id,
                            productId: item.product_id,
                            quantity: item.quantity,
                            price: item.price
                        }))
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Refund registered successfully'
                }
            },
            'Refund registered successfully',
            201
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to register refund',
                description: {
                    action: 'Register Refund Error',
                    details: {
                        error: e.message,
                        orderId: parseInt(req.params.orderId as string),
                        refundData: {
                            reason: req.body.reason,
                            refundAmount: req.body.refundAmount,
                            total: req.body.total
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
}; 