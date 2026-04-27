export const registerOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const {
            memberId,
            orderDate,
            paymentMethod,
            orderStatus,
            productList
        } = req.body;

        const totalAmount = productList.reduce((total: number, product: Product) => total + product.price * product.stock_quantity, 0);
        
        let orderData: any = {
            member_id: parseInt(memberId),
            order_number: uuid4(),
            order_date: new Date(orderDate),
            total_amount: totalAmount,
            status: orderStatus,
            payment_method: paymentMethod,
        };
        const order = await orderRepo.create(orderData);

        const orderItemData = productList.map((product: Product) => ({
            order_id: order.order_id,
            product_id: product.product_id,
            quantity: product.stock_quantity,
            unit_price: product.price,
            line_total: product.price * product.stock_quantity
        }));
        await orderItemRepo.bulkCreate(orderItemData);

        const productIdList = productList.map((product: Product) => product.product_id);
        const products: Product[] = await productRepo.findAll({
            where: {
                product_id: productIdList
            }
        }) || [];
        const productMap = new Map(products.map((product) => [product.product_id, product]));
        
        let totalStockUpdated = 0;
        for (const orderProduct of productList) {
            const product = productMap.get(orderProduct.product_id);
            if (product) {
                product.stock_quantity -= orderProduct.stock_quantity;
                await product.save();
                totalStockUpdated += orderProduct.stock_quantity;
            }
        }

        return returnSuccess(
            res,
            { orderId: order.order_id },
            {
                action: 'Register Order',
                details: {
                    orderId: order.order_id,
                    memberId,
                    orderNumber: order.order_number,
                    orderDate: new Date(orderDate),
                    totalAmount,
                    orderStatus,
                    paymentMethod,
                    products: {
                        count: productList.length,
                        totalStockUpdated,
                        productIds: productIdList
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Order registered successfully'
                }
            },
            'Order registered successfully',
            201
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to register order',
                description: {
                    action: 'Register Order Error',
                    details: {
                        error: e.message,
                        memberId: req.body.memberId,
                        orderDate: req.body.orderDate,
                        orderStatus: req.body.orderStatus,
                        paymentMethod: req.body.paymentMethod,
                        productCount: req.body.productList?.length,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const searchOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);
        const memberId = req.query?.memberId;
        const orderStatus = req.query?.orderStatus;
        const paymentMethod = req.query?.paymentMethod;
        const fromDate = req.query?.fromDate;
        const toDate = req.query?.toDate;

        let whereClause: any = {};
        if(memberId) {
            whereClause.member_id = memberId;
        }
        if (orderStatus) {
            whereClause.status = orderStatus;
        }
        if (paymentMethod) {
            whereClause.payment_method = paymentMethod;
        }
        if (fromDate && !toDate) {
            whereClause.order_date = { [Op.gte]: fromDate };
        }
        if (!fromDate && toDate) {
            whereClause.order_date = { [Op.lte]: toDate };
        }
        if (fromDate && toDate) {
            whereClause.order_date = { [Op.between]: [fromDate, toDate] };
        }

        const data = await orderRepo.paginate({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            where: whereClause,
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Search Orders',
                details: {
                    page,
                    pageSize,
                    filters: {
                        memberId,
                        orderStatus,
                        paymentMethod,
                        dateRange: {
                            from: fromDate,
                            to: toDate
                        }
                    },
                    results: {
                        total: data.total,
                        currentPage: data.currentPage,
                        totalPages: data.totalPages,
                        hasNextPage: data.hasNextPage,
                        hasPrevPage: data.hasPrevPage
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Orders retrieved successfully'
                }
            },
            'Orders retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search orders',
                description: {
                    action: 'Search Orders Error',
                    details: {
                        error: e.message,
                        page: req.query.page,
                        pageSize: req.query.pageSize,
                        memberId: req.query.memberId,
                        orderStatus: req.query.orderStatus,
                        paymentMethod: req.query.paymentMethod,
                        fromDate: req.query.fromDate,
                        toDate: req.query.toDate,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { orderId } = req.params;

        const data = await orderRepo.findOne({
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
                order_id: orderId,
            },
        });

        if (!data) {
            return returnError(
                res,
                {
                    orderId,
                    timestamp: new Date().toISOString()
                },
                {
                    action: 'Get Order',
                    details: {
                        orderId,
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Order not found'
                    }
                },
                'Order not found',
                404
            );
        }

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Order',
                details: {
                    orderId,
                    orderNumber: data.order_number,
                    orderDate: data.order_date,
                    totalAmount: data.total_amount,
                    status: data.status,
                    paymentMethod: data.payment_method,
                    items: {
                        count: data.order_items?.length || 0,
                        totalQuantity: data.order_items?.reduce((sum: number, item: OrderItem) => sum + item.quantity, 0) || 0
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Order retrieved successfully'
                }
            },
            'Order retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get order',
                description: {
                    action: 'Get Order Error',
                    details: {
                        error: e.message,
                        orderId: req.params.orderId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { orderId } = req.params;
        const {
            memberId,
            orderDate,
            paymentMethod,
            orderStatus,
        } = req.body;

        // Check if order exists
        const existingOrder = await orderRepo.findOne({
            where: {
                order_id: orderId,
            },
        });

        if (!existingOrder) {
            return returnError(
                res,
                {
                    orderId,
                    timestamp: new Date().toISOString()
                },
                {
                    action: 'Update Order',
                    details: {
                        orderId,
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Order not found'
                    }
                },
                'Order not found',
                404
            );
        }

        await orderRepo.update(
            {
                member_id: memberId,
                order_date: new Date(orderDate),
                payment_method: paymentMethod,
                status: orderStatus,
            },
            {
                where: {
                    order_id: orderId,
                },
                individualHooks: true,
            }
        );

        return returnSuccess(
            res,
            { orderId },
            {
                action: 'Update Order',
                details: {
                    orderId,
                    changes: {
                        memberId,
                        orderDate: new Date(orderDate),
                        paymentMethod,
                        orderStatus,
                        previousStatus: existingOrder.status
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Order updated successfully'
                }
            },
            'Order updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update order',
                description: {
                    action: 'Update Order Error',
                    details: {
                        error: e.message,
                        orderId: req.params.orderId,
                        memberId: req.body.memberId,
                        orderDate: req.body.orderDate,
                        orderStatus: req.body.orderStatus,
                        paymentMethod: req.body.paymentMethod,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const deleteOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { orderId } = req.params;

        // Check if order exists
        const existingOrder = await orderRepo.findOne({
            where: {
                order_id: orderId,
            },
        });

        if (!existingOrder) {
            return returnError(
                res,
                {
                    orderId,
                    timestamp: new Date().toISOString()
                },
                {
                    action: 'Delete Order',
                    details: {
                        orderId,
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Order not found'
                    }
                },
                'Order not found',
                404
            );
        }

        const orderItems: OrderItem[] = await orderItemRepo.findAll({
            attributes: ['product_id', 'quantity'],
            where: {
                order_id: orderId,
            },
        }) || [];

        let totalStockUpdated = 0;
        for (let orderItem of orderItems) {
            const product = await productRepo.findOne({
                where: {
                    product_id: orderItem.product_id,
                },
            });
            if (product) {
                await product.update({
                    stock_quantity: product.stock_quantity + orderItem.quantity
                });
                totalStockUpdated += orderItem.quantity;
            }
        }

        await orderItemRepo.destroy({
            where: {
                order_id: orderId
            }
        });

        await orderRepo.destroy({
            where: {
                order_id: orderId
            }
        });

        return returnSuccess(
            res,
            { orderId },
            {
                action: 'Delete Order',
                details: {
                    orderId,
                    orderNumber: existingOrder.order_number,
                    orderDate: existingOrder.order_date,
                    totalAmount: existingOrder.total_amount,
                    status: existingOrder.status,
                    paymentMethod: existingOrder.payment_method,
                    changes: {
                        itemsDeleted: orderItems.length,
                        totalStockUpdated,
                        productIds: orderItems.map(item => item.product_id)
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Order deleted successfully'
                }
            },
            'Order deleted successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to delete order',
                description: {
                    action: 'Delete Order Error',
                    details: {
                        error: e.message,
                        orderId: req.params.orderId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const refundOrder = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { orderId } = req.params;

        // Check if order exists
        const existingOrder = await orderRepo.findOne({
            where: {
                order_id: orderId,
            },
        });

        if (!existingOrder) {
            return returnError(
                res,
                {
                    orderId,
                    timestamp: new Date().toISOString()
                },
                {
                    action: 'Refund Order',
                    details: {
                        orderId,
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Order not found'
                    }
                },
                'Order not found',
                404
            );
        }

        const orderItems: OrderItem[] = await orderItemRepo.findAll({
            attributes: ['product_id', 'quantity', 'unit_price'],
            where: {
                order_id: orderId,
            },
        }) || [];

        let totalStockUpdated = 0;
        let totalRefundAmount = 0;
        for (let orderItem of orderItems) {
            const product = await productRepo.findOne({
                where: {
                    product_id: orderItem.product_id,
                },
            });
            if (product) {
                await product.update({
                    stock_quantity: product.stock_quantity + orderItem.quantity
                });
                totalStockUpdated += orderItem.quantity;
                totalRefundAmount += orderItem.quantity * orderItem.unit_price;
            }
        }

        await orderItemRepo.destroy({
            where: {
                order_id: orderId
            }
        });

        await orderRepo.update(
            {
                status: "cancelled",
            },
            {
                where: {
                    order_id: orderId,
                },
                individualHooks: true,
            }
        );

        return returnSuccess(
            res,
            { orderId },
            {
                action: 'Refund Order',
                details: {
                    orderId,
                    orderNumber: existingOrder.order_number,
                    orderDate: existingOrder.order_date,
                    totalAmount: existingOrder.total_amount,
                    previousStatus: existingOrder.status,
                    changes: {
                        itemsRefunded: orderItems.length,
                        totalStockUpdated,
                        totalRefundAmount,
                        productIds: orderItems.map(item => item.product_id)
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Order refunded successfully'
                }
            },
            'Order refunded successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to refund order',
                description: {
                    action: 'Refund Order Error',
                    details: {
                        error: e.message,
                        orderId: req.params.orderId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
}; 