export const getAllPeriod = async (req: Request, res: Response, next: NextFunction) => {
    try {
        logger.info({
            message: 'Starting commission period retrieval process',
            description: {
                action: 'Get All Commission Periods',
                details: {
                    timestamp: new Date().toISOString()
                }
            }
        });

        const data = await commissionPeriodRepo.findAll({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
        });

        if (!data) {
            logger.warn({
                message: 'No commission periods found',
                description: {
                    action: 'Get All Commission Periods',
                    details: {
                        timestamp: new Date().toISOString(),
                        status: 'not_found'
                    }
                }
            });
            return returnSuccess(
                res,
                [],
                {
                    action: 'Get All Commission Periods',
                    details: {
                        count: 0,
                        timestamp: new Date().toISOString(),
                        status: 'not_found',
                        message: 'No commission periods found'
                    }
                },
                'No commission periods found',
                200
            );
        }

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Commission Periods',
                details: {
                    count: data.length,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Commission periods retrieved successfully'
                }
            },
            'Commission periods retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to retrieve commission periods',
                description: {
                    action: 'Get Commission Periods Error',
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