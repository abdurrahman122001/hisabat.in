export const getAllPlan = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await commissionPlanRepo.findAll({
            attributes: {
                exclude: ['created_at', 'updated_at']
            }
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Commission Plans',
                details: {
                    count: data ? data.length : 0,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Commission plans retrieved successfully'
                }
            },
            'Commission plans retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to retrieve commission plans',
                description: {
                    action: 'Get Commission Plans Error',
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