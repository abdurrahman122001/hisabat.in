export const getAllProductCategory = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await productCategoryRepo.findAll({
            attributes: ['product_category_id', 'category_name']
        }) || [];

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Product Categories',
                details: {
                    count: data.length,
                    categories: data.map(category => ({
                        id: category.product_category_id,
                        name: category.category_name
                    })),
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Product categories retrieved successfully'
                }
            },
            'Product categories retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get product categories',
                description: {
                    action: 'Get All Product Categories Error',
                    details: {
                        error: e.message,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const registerProduct = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const {
            productName,
            productCode,
            categoryId,
            productDescription,
            productPrice,
            productQuantity,
            productStatus,
        } = req.body;

        let productData: any = {
            product_code: productCode,
            product_name: productName,
            product_category_id: categoryId || null,
            description: productDescription,
            price: productPrice,
            stock_quantity: productQuantity,
            status: productStatus === 'active' ? true : false,
        };

        const product = await productRepo.create(productData);

        return returnSuccess(
            res,
            { productId: product.product_id },
            {
                action: 'Register Product',
                details: {
                    productId: product.product_id,
                    productCode,
                    productName,
                    categoryId,
                    productDescription,
                    productPrice,
                    productQuantity,
                    productStatus,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Product registered successfully'
                }
            },
            'Product registered successfully',
            201
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to register product',
                description: {
                    action: 'Register Product Error',
                    details: {
                        error: e.message,
                        productCode: req.body.productCode,
                        productName: req.body.productName,
                        categoryId: req.body.categoryId,
                        productPrice: req.body.productPrice,
                        productQuantity: req.body.productQuantity,
                        productStatus: req.body.productStatus,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const searchProduct = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);
        const productCode = req.query?.productCode;
        const productName = req.query?.productName;
        const categoryId = req.query?.categoryId;
        const productStatus = req.query?.productStatus;

        let productWhereClause: any = {};
        if (productCode) {
            productWhereClause.product_code = productCode;
        }
        if (productName) {
            productWhereClause.product_code = { [Op.like]: `%${productName}%` };
        }
        if (categoryId) {
            productWhereClause.product_category_id = categoryId;
        }
        if (productStatus) {
            productWhereClause.status = productStatus === 'active' ? true : false;
        }

        const data = await productRepo.paginate({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            include: [
                {
                    model: ProductCategory,
                    as: 'product_category',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    },
                },
            ],
            where: productWhereClause,
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Search Products',
                details: {
                    page,
                    pageSize,
                    filters: {
                        productCode,
                        productName,
                        categoryId,
                        productStatus
                    },
                    results: {
                        total: data.total,
                        currentPage: data.currentPage,
                        totalPages: data.totalPages,
                        hasNextPage: data.hasNextPage,
                        hasPrevPage: data.hasPrevPage,
                        activeProducts: data.data.filter(product => product.status).length,
                        inactiveProducts: data.data.filter(product => !product.status).length,
                        productsWithCategory: data.data.filter(product => product.product_category).length,
                        productsWithoutCategory: data.data.filter(product => !product.product_category).length,
                        totalStock: data.data.reduce((sum, product) => sum + product.stock_quantity, 0),
                        averagePrice: data.data.length > 0 
                            ? data.data.reduce((sum, product) => sum + product.price, 0) / data.data.length 
                            : 0
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Products retrieved successfully'
                }
            },
            'Products retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search products',
                description: {
                    action: 'Search Products Error',
                    details: {
                        error: e.message,
                        page: req.query.page,
                        pageSize: req.query.pageSize,
                        filters: {
                            productCode: req.query.productCode,
                            productName: req.query.productName,
                            categoryId: req.query.categoryId,
                            productStatus: req.query.productStatus
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getActiveProduct = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await productRepo.findAll({
            attributes: ['product_id', 'product_name', 'price', 'stock_quantity'],
            where: {
                status: true,
            },
        }) || [];

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Active Products',
                details: {
                    count: data.length,
                    products: data.map(product => ({
                        id: product.product_id,
                        name: product.product_name,
                        price: product.price,
                        stockQuantity: product.stock_quantity
                    })),
                    statistics: {
                        totalProducts: data.length,
                        totalStock: data.reduce((sum, product) => sum + product.stock_quantity, 0),
                        averagePrice: data.length > 0 
                            ? data.reduce((sum, product) => sum + product.price, 0) / data.length 
                            : 0
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Active products retrieved successfully'
                }
            },
            'Active products retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get active products',
                description: {
                    action: 'Get Active Products Error',
                    details: {
                        error: e.message,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getProduct = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { productId } = req.params;
        const data = await productRepo.findOne({
            attributes: {
                exclude: ['created_at', 'updated_at'],
            },
            include: {
                model: ProductCategory,
                as: 'product_category',
                attributes: ['product_category_id', 'category_name'],
            },
            where: {
                product_id: parseInt(productId as string),
            },
        });

        if (!data) {
            return returnError(
                res,
                {
                    action: 'Get Product',
                    details: {
                        productId: parseInt(productId as string),
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Product not found'
                    }
                },
                'Product not found',
                404
            );
        }

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Product',
                details: {
                    productId: parseInt(productId as string),
                    product: {
                        id: data.product_id,
                        code: data.product_code,
                        name: data.product_name,
                        category: data.product_category ? {
                            id: data.product_category.product_category_id,
                            name: data.product_category.category_name
                        } : null,
                        description: data.description,
                        price: data.price,
                        stockQuantity: data.stock_quantity,
                        status: data.status
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Product retrieved successfully'
                }
            },
            'Product retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get product',
                description: {
                    action: 'Get Product Error',
                    details: {
                        error: e.message,
                        productId: parseInt(req.params.productId as string),
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateProduct = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { productId } = req.params;
        const {
            productName,
            productCode,
            categoryId,
            productDescription,
            productPrice,
            productQuantity,
            productStatus,
        } = req.body;

        const [updatedCount] = await productRepo.update(
            {
                product_name: productName,
                product_code: productCode,
                product_category_id: categoryId ? categoryId : null,
                description: productDescription,
                price: productPrice,
                stock_quantity: productQuantity,
                status: productStatus === 'active' ? true : false
            },
            {
                where: {
                    product_id: parseInt(productId as string),
                },
                individualHooks: true,
            }
        );

        if (updatedCount === 0) {
            return returnError(
                res,
                {
                    action: 'Update Product',
                    details: {
                        productId: parseInt(productId as string),
                        timestamp: new Date().toISOString(),
                        status: 'error',
                        message: 'Product not found'
                    }
                },
                'Product not found',
                404
            );
        }

        return returnSuccess(
            res,
            { updatedCount },
            {
                action: 'Update Product',
                details: {
                    productId: parseInt(productId as string),
                    updates: {
                        productName,
                        productCode,
                        categoryId,
                        productDescription,
                        productPrice,
                        productQuantity,
                        productStatus
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Product updated successfully'
                }
            },
            'Product updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update product',
                description: {
                    action: 'Update Product Error',
                    details: {
                        error: e.message,
                        productId: parseInt(req.params.productId as string),
                        updates: {
                            productName: req.body.productName,
                            productCode: req.body.productCode,
                            categoryId: req.body.categoryId,
                            productPrice: req.body.productPrice,
                            productQuantity: req.body.productQuantity,
                            productStatus: req.body.productStatus
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
}; 