export const updateMemberAutoPurchaseStatus = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const { auto_purchase } = req.body;

        await memberRepo.update(
            {
                auto_purchase: auto_purchase
            },
            {
                where: {
                    member_id: memberId
                },
                individualHooks: true,
            }
        );

        return returnSuccess(
            res,
            { memberId, auto_purchase },
            {
                action: 'Update Member Auto Purchase Status',
                details: {
                    memberId,
                    auto_purchase,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member auto purchase status updated successfully'
                }
            },
            'Member auto purchase status updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update member auto purchase status',
                description: {
                    action: 'Update Member Auto Purchase Status Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        auto_purchase: req.body.auto_purchase,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const searchMember = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);
        const memberId = req.query?.memberId;
        const name = req.query?.name;
        const introducerId = req.query?.introducerId;
        const state = req.query?.state;
        const fromDate = req.query?.fromDate;
        const toDate = req.query?.toDate;

        let memberWhereClause: any = {};
        let memberAddressWhereClause: any = {};
        let memberPositionWhereClause: any = {};

        if (memberId) {
            memberWhereClause.member_id = memberId;
        }
        if (name) {
            memberWhereClause.name = { [Op.like]: `%${name}%` };
        }
        if (introducerId) {
            memberPositionWhereClause.introducer_id = introducerId;
        }
        if (state) {
            memberAddressWhereClause.state = { [Op.like]: `%${state}%` };
        }
        if (fromDate && !toDate) {
            memberWhereClause.contract_date = { [Op.gte]: fromDate };
        }
        if (!fromDate && toDate) {
            memberWhereClause.contract_date = { [Op.lte]: toDate };
        }
        if (fromDate && toDate) {
            memberWhereClause.contract_date = { [Op.between]: [fromDate, toDate] };
        }

        const data = await memberRepo.paginate({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            include: [
                {
                    model: MemberAddress,
                    as: 'member_address',
                    attributes: {
                        exclude: ['country', 'created_at', 'updated_at']
                    },
                    where: memberAddressWhereClause,
                },
                {
                    model: MemberPosition,
                    as: 'member_position',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    },
                    where: memberPositionWhereClause,
                },
                {
                    model: MemberBankAccount,
                    as: 'member_bank_account',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    }
                }
            ],
            where: memberWhereClause,
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Search Members',
                details: {
                    page,
                    pageSize,
                    filters: {
                        memberId,
                        name,
                        introducerId,
                        state,
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
                        hasPrevPage: data.hasPrevPage,
                        activeMembers: data.data.filter(member => member.status).length,
                        inactiveMembers: data.data.filter(member => !member.status).length,
                        membersWithAddress: data.data.filter(member => member.member_address).length,
                        membersWithBankAccount: data.data.filter(member => member.member_bank_account).length,
                        membersWithPosition: data.data.filter(member => member.member_position).length
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Members retrieved successfully'
                }
            },
            'Members retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search members',
                description: {
                    action: 'Search Members Error',
                    details: {
                        error: e.message,
                        page: req.query.page,
                        pageSize: req.query.pageSize,
                        filters: {
                            memberId: req.query.memberId,
                            name: req.query.name,
                            introducerId: req.query.introducerId,
                            state: req.query.state,
                            fromDate: req.query.fromDate,
                            toDate: req.query.toDate
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getAllMemberBankAccount = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await memberBankRepo.findAll({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            include: [
                {
                    model: Member,
                    as: "member",
                    attributes: ["member_id", "name", "email", "phone", "status"]
                }
            ]
        }) || [];

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Member Bank Accounts',
                details: {
                    count: data.length,
                    bankAccounts: data.map(account => ({
                        id: account.member_bank_account_id,
                        memberId: account.member_id,
                        memberName: account.member?.name,
                        bankName: account.bank_name,
                        accountNumber: account.account_number,
                        accountName: account.account_name,
                        status: account.status
                    })),
                    statistics: {
                        totalAccounts: data.length,
                        activeAccounts: data.filter(account => account.status).length,
                        inactiveAccounts: data.filter(account => !account.status).length,
                        activeMembers: data.filter(account => account.member?.status).length,
                        inactiveMembers: data.filter(account => account.member && !account.member.status).length
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member bank accounts retrieved successfully'
                }
            },
            'Member bank accounts retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get member bank accounts',
                description: {
                    action: 'Get All Member Bank Accounts Error',
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