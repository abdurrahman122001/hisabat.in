import { Op } from 'sequelize';
import { Member } from '../models/member.model';
import { MemberAddress } from '../models/member_address.model';
import { MemberPosition } from '../models/member_position.model';
import { OrganizationRelationship } from '../models/organization_relationship.model';
import { MemberBankAccount } from '../models/member_bank_account.model';
import { OrderItem } from '../models/order_item.model';
import { Order } from '../models/order.model';
import { Product } from '../models/product.model';
import { MemberCommission } from '../models/member_commission.model';
import { Adjustment } from '../models/adjustment.model';

interface CommissionWhereClause {
    member_id?: string | number;
    calculated_at?: {
        [Op.gte]?: string;
        [Op.lte]?: string;
        [Op.between]?: [string, string];
    };
}

interface MemberWhereClause {
    cooling_off: boolean;
    name?: {
        [Op.like]: string;
    };
}

interface CommissionData {
    data: any[];
    pagination: {
        total: number;
        page: number;
        pageSize: number;
    };
}

export const searchCommission = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);
        const memberId = req.query?.memberId;
        const memberName = req.query?.memberName;
        const fromDate = req.query?.fromDate;
        const toDate = req.query?.toDate;
        let commissionWhereClause: any = {};
        let memberWhereClause: any = {cooling_off: false};

        if (memberId) {
            commissionWhereClause.member_id = memberId;
        }
        if (memberName) {
            memberWhereClause.name = { [Op.like]: `%${memberName}%` };
        }
        if (fromDate && !toDate) {
            commissionWhereClause.calculated_at = { [Op.gte]: fromDate };
        }
        if (!fromDate && toDate) {
            commissionWhereClause.calculated_at = { [Op.lte]: toDate };
        }
        if (fromDate && toDate) {
            commissionWhereClause.calculated_at = { [Op.between]: [fromDate, toDate] };
        }

        const data = await commissionRepo.paginate({
            include: [
                {
                    model: Member,
                    as: 'member',
                    attributes: ['name'],
                    where: memberWhereClause,
                },
            ],
            where: commissionWhereClause,
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Search Commission',
                details: {
                    count: data.total,
                    page,
                    pageSize,
                    filters: {
                        memberId,
                        memberName,
                        fromDate,
                        toDate
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Commission search completed successfully'
                }
            },
            'Commission search completed successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search commission',
                description: {
                    action: 'Search Commission Error',
                    details: {
                        error: e.message,
                        filters: {
                            memberId: req.query?.memberId,
                            memberName: req.query?.memberName,
                            fromDate: req.query?.fromDate,
                            toDate: req.query?.toDate
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const searchAdjustment = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);
        const memberId = req.query?.memberId;
        const memberName = req.query?.memberName;
        const fromDate = req.query?.fromDate;
        const toDate = req.query?.toDate;
        let adjustmentWhereClause: any = {};
        let memberWhereClause: any = {};

        if (memberId) {
            adjustmentWhereClause.member_id = memberId;
        }
        if (memberName) {
            memberWhereClause.name = { [Op.like]: `%${memberName}%` };
        }
        if (fromDate && !toDate) {
            adjustmentWhereClause.calculated_at = { [Op.gte]: fromDate };
        }
        if (!fromDate && toDate) {
            adjustmentWhereClause.calculated_at = { [Op.lte]: toDate };
        }
        if (fromDate && toDate) {
            adjustmentWhereClause.calculated_at = { [Op.between]: [fromDate, toDate] };
        }

        const data = await adjustmentRepo.paginate({
            include: [
                {
                    model: Member,
                    as: 'member',
                    attributes: ['name'],
                    where: memberWhereClause,
                },
            ],
            where: adjustmentWhereClause,
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Search Adjustment',
                details: {
                    count: data.total,
                    page,
                    pageSize,
                    filters: {
                        memberId,
                        memberName,
                        fromDate,
                        toDate
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Adjustment search completed successfully'
                }
            },
            'Adjustment search completed successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search adjustment',
                description: {
                    action: 'Search Adjustment Error',
                    details: {
                        error: e.message,
                        filters: {
                            memberId: req.query?.memberId,
                            memberName: req.query?.memberName,
                            fromDate: req.query?.fromDate,
                            toDate: req.query?.toDate
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getMembersOrganization = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await memberRepo.findAll({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            include: [
                {
                    model: MemberAddress,
                    as: 'member_address',
                    attributes: ['state'],
                },
                {
                    model: MemberPosition,
                    as: 'member_position',
                    attributes: ['introducer_id', 'contract_plan'],
                },
                {
                    model: OrganizationRelationship,
                    as: 'parents',
                    attributes: ['parent_member_id', 'depth'],
                    where: {
                        depth: 1
                    },
                    required: false
                }
            ]
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Members Organization',
                details: {
                    count: data ? data.length : 0,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Members organization data retrieved successfully'
                }
            },
            'Members organization data retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get members organization',
                description: {
                    action: 'Get Members Organization Error',
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

export const getMemberOrganization = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const allRelationship = await organizationRelationshipRepo.findAll({
            attributes: ['parent_member_id', 'child_member_id', 'depth'],
            where: {
                [Op.and]: [
                    {
                        [Op.or]: [
                            { parent_member_id: memberId },
                            { child_member_id: memberId },
                        ],
                    },
                    {
                        depth: {
                            [Op.in]: [0, 1, 2]
                        }
                    }
                ],
            },
        }) || [];

        const allParentIds = allRelationship
            .filter((rel) => rel.child_member_id === parseInt(memberId))
            .map((rel) => rel.parent_member_id);
        const allChildIds = allRelationship
            .filter((rel) => rel.parent_member_id === parseInt(memberId))
            .map((rel) => rel.child_member_id);
        const allIds = [...allParentIds, ...allChildIds];
        const uniqueallIds = [...new Set(allIds)];

        const topParent = getTopParentMemberId(memberId, allRelationship);

        const data = await memberRepo.findAll({
            attributes: {
                exclude: ['created_at', 'updated_at']
            },
            include: [
                {
                    model: MemberAddress,
                    as: 'member_address',
                    attributes: ['state'],
                },
                {
                    model: MemberPosition,
                    as: 'member_position',
                    attributes: ['introducer_id', 'contract_plan'],
                },
                {
                    model: OrganizationRelationship,
                    as: 'parents',
                    attributes: ['parent_member_id', 'depth'],
                    where: {
                        depth: 1
                    },
                    required: false
                }
            ],
            where: {
                member_id: uniqueallIds,
            },
        });

        return returnSuccess(
            res,
            {
                memberList: data,
                topParent: topParent,
            },
            {
                action: 'Get Member Organization',
                details: {
                    memberId,
                    memberCount: data ? data.length : 0,
                    relationshipCount: allRelationship.length,
                    parentCount: allParentIds.length,
                    childCount: allChildIds.length,
                    uniqueMemberCount: uniqueallIds.length,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member organization data retrieved successfully'
                }
            },
            'Member organization data retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get member organization',
                description: {
                    action: 'Get Member Organization Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
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
        let memberPositioinWhereClause: any = {};

        if (memberId) {
            memberWhereClause.member_id = memberId;
        }
        if (name) {
            memberWhereClause.name = { [Op.like]: `%${name}%` };
        }
        if (introducerId) {
            memberPositioinWhereClause.introducer_id = introducerId;
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
                    where: memberPositioinWhereClause,
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
                action: 'Search Member',
                details: {
                    count: data.total,
                    page,
                    pageSize,
                    filters: {
                        memberId,
                        name,
                        introducerId,
                        state,
                        fromDate,
                        toDate
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member search completed successfully'
                }
            },
            'Member search completed successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to search member',
                description: {
                    action: 'Search Member Error',
                    details: {
                        error: e.message,
                        filters: {
                            memberId: req.query?.memberId,
                            name: req.query?.name,
                            introducerId: req.query?.introducerId,
                            state: req.query?.state,
                            fromDate: req.query?.fromDate,
                            toDate: req.query?.toDate
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getAllMember = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const data = await memberRepo.findAll({
            attributes: ['member_id', 'name', 'status']
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Members',
                details: {
                    count: data ? data.length : 0,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'All members retrieved successfully'
                }
            },
            'All members retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get all members',
                description: {
                    action: 'Get All Members Error',
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

export const registerMember = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const {
            name,
            email,
            phone,
            contractDate,
            country,
            postalCode,
            state,
            city,
            address1,
            address2,
            introducerId,
            contractPlan,
            bankName,
            branchName,
            accountType,
            accountNumber,
            accountHolder,
        } = req.body;

        const parentId = await findFirstDepthOneMember([introducerId]);
        let memberData: any = {
            contract_date: new Date(contractDate),
            name: name,
            email: email ? email : null,
            phone: phone ? phone : null,
        };
        const member = await memberRepo.create(memberData);

        let memberAddressData: any = {
            member_id: member.member_id,
            country: country,
            postal_code: postalCode,
            state: state,
            city: city,
            address1: address1,
            address2: address2 ? address2 : null,
        };
        await memberAddressRepo.create(memberAddressData);

        let memberBankData: any = {
            member_id: member.member_id,
            bank_name: bankName,
            branch_name: branchName,
            account_number: accountNumber,
            account_type: accountType,
            account_holder: accountHolder,
        };
        await memberBankRepo.create(memberBankData);

        let memberPositionData: any = {
            member_id: member.member_id,
            introducer_id: introducerId,
            contract_plan: contractPlan,
        };
        await memberPositionRepo.create(memberPositionData);

        const allRelationships = await organizationRelationshipRepo.findAll({
            attributes: {
                exclude: ['created_at']
            },
            where: {
                child_member_id: parentId
            }
        }) || [];

        let orgRelationshipData = allRelationships.map((relationship) => ({
            parent_member_id: relationship.parent_member_id,
            child_member_id: member.member_id,
            depth: relationship.depth + 1,
        }));
        orgRelationshipData.push({
            parent_member_id: member.member_id,
            child_member_id: member.member_id,
            depth: 0
        });
        await organizationRelationshipRepo.bulkCreate(orgRelationshipData);

        return returnSuccess(
            res,
            { memberId: member.member_id },
            {
                action: 'Register Member',
                details: {
                    memberId: member.member_id,
                    name,
                    introducerId,
                    contractPlan,
                    address: {
                        country,
                        state,
                        city
                    },
                    bankDetails: {
                        bankName,
                        branchName,
                        accountType
                    },
                    relationships: {
                        parentId,
                        totalRelationships: orgRelationshipData.length
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member registered successfully'
                }
            },
            'Member registered successfully',
            201
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to register member',
                description: {
                    action: 'Register Member Error',
                    details: {
                        error: e.message,
                        memberData: {
                            name: req.body.name,
                            email: req.body.email,
                            introducerId: req.body.introducerId,
                            contractPlan: req.body.contractPlan
                        },
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getMemberMainData = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const data = await memberRepo.findOne({
            where: {
                member_id: memberId,
            },
            attributes: ['name', 'email', 'phone', 'status', 'cooling_off', 'auto_purchase'],
            include: [
                {
                    model: MemberAddress,
                    as: 'member_address',
                    attributes: {
                        exclude: ['created_at', 'updated_at'],
                    }
                },
                {
                    model: MemberBankAccount,
                    as: 'member_bank_account',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    }
                }
            ]
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Member Main Data',
                details: {
                    memberId,
                    hasData: !!data,
                    memberStatus: data?.status,
                    hasAddress: !!data?.member_address,
                    hasBankAccount: !!data?.member_bank_account,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member main data retrieved successfully'
                }
            },
            'Member main data retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get member main data',
                description: {
                    action: 'Get Member Main Data Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
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
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get All Member Bank Accounts',
                details: {
                    count: data ? data.length : 0,
                    activeMembers: data?.filter(item => item.member?.status === 'active').length || 0,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'All member bank accounts retrieved successfully'
                }
            },
            'All member bank accounts retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get all member bank accounts',
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

export const updateMemberMainData = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const {
            name,
            email,
            phone,
            addressId,
            country,
            postalCode,
            state,
            city,
            address1,
            address2,
            bankAccountId,
            bankName,
            branchName,
            accountType,
            accountNumber,
            accountHolder
        } = req.body;

        await memberRepo.update(
            {
                name: name,
                email: email ? email : null,
                phone: phone ? phone : null
            },
            {
                where: {
                    member_id: memberId
                },
                individualHooks: true
            }
        );

        await memberAddressRepo.update(
            {
                country: country,
                postal_code: postalCode,
                state: state,
                city: city,
                address1: address1,
                address2: address2 ? address2 : null,
            },
            {
                where: {
                    address_id: addressId
                },
                individualHooks: true,
            }
        );

        await memberBankRepo.update(
            {
                bank_name: bankName,
                branch_name: branchName,
                account_type: accountType,
                account_number: accountNumber,
                acount_holder: accountHolder,
            },
            {
                where: {
                    bank_account_id: bankAccountId
                },
                individualHooks: true,
            }
        );

        return returnSuccess(
            res,
            { memberId },
            {
                action: 'Update Member Main Data',
                details: {
                    memberId,
                    updatedFields: {
                        member: {
                            name,
                            email,
                            phone
                        },
                        address: {
                            country,
                            state,
                            city,
                            address1,
                            address2
                        },
                        bank: {
                            bankName,
                            branchName,
                            accountType,
                            accountNumber,
                            accountHolder
                        }
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member main data updated successfully'
                }
            },
            'Member main data updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update member main data',
                description: {
                    action: 'Update Member Main Data Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        addressId: req.body.addressId,
                        bankAccountId: req.body.bankAccountId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getMemberPositionData = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const data = await memberRepo.findOne({
            attributes: ['member_id', 'contract_date'],
            include: [
                {
                    model: MemberPosition,
                    as: 'member_position',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    },
                    include: [
                        {
                            model: Member,
                            as: 'introducer',
                            attributes: {
                                exclude: ['created_at', 'updated_at'],
                            }
                        }
                    ]
                }
            ],
            where: {
                member_id: memberId,
            }
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Member Position Data',
                details: {
                    memberId,
                    hasData: !!data,
                    hasPosition: !!data?.member_position,
                    hasIntroducer: !!data?.member_position?.introducer,
                    contractDate: data?.contract_date,
                    introducerId: data?.member_position?.introducer_id,
                    contractPlan: data?.member_position?.contract_plan,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member position data retrieved successfully'
                }
            },
            'Member position data retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get member position data',
                description: {
                    action: 'Get Member Position Data Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateMemberPositionData = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const {
            contractDate,
            positionId,
            contractPlan,
        } = req.body;

        await memberRepo.update(
            {
                contract_date: new Date(contractDate),
            },
            {
                where: {
                    member_id: memberId,
                },
                individualHooks: true,
            }
        );

        await memberPositionRepo.update(
            {
                contract_plan: contractPlan,
            },
            {
                where: {
                    position_id: positionId
                },
                individualHooks: true,
            }
        );

        return returnSuccess(
            res,
            { memberId, positionId },
            {
                action: 'Update Member Position Data',
                details: {
                    memberId,
                    positionId,
                    updatedFields: {
                        member: {
                            contractDate: new Date(contractDate)
                        },
                        position: {
                            contractPlan
                        }
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member position data updated successfully'
                }
            },
            'Member position data updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update member position data',
                description: {
                    action: 'Update Member Position Data Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        positionId: req.body.positionId,
                        contractPlan: req.body.contractPlan,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getAllIntroducers = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const allChildRelationship = await organizationRelationshipRepo.findAll({
            attributes: ['child_member_id'],
            where: {
                parent_member_id: memberId,
            }
        }) || [];
        const allChildIds = allChildRelationship.map((relationship) => relationship.child_member_id);

        const allMembers = await memberRepo.findAll({
            attributes: ['member_id']
        }) || [];
        const allMemberIds = allMembers.map((member) => member.member_id);

        const availableIds = allMemberIds.filter((item) => !allChildIds.includes(item));
        const availableMembers = await memberRepo.findAll({
            attributes: ['member_id', 'name'],
            where: {
                member_id: availableIds
            }
        });

        return returnSuccess(
            res,
            availableMembers,
            {
                action: 'Get All Available Introducers',
                details: {
                    memberId,
                    totalMembers: allMemberIds.length,
                    existingChildren: allChildIds.length,
                    availableIntroducers: availableMembers.length,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Available introducers retrieved successfully'
                }
            },
            'Available introducers retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get available introducers',
                description: {
                    action: 'Get Available Introducers Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateOrganizationRelationship = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const { newParentMemberId } = req.body;

        const allChildRelationship = await organizationRelationshipRepo.findAll({
            where: {
                parent_member_id: memberId
            },
            attributes: ['child_member_id', 'depth'],
        }) || [];
        const allChildId = allChildRelationship.map((relationship) => relationship.child_member_id);
        
        const subRelationship = await organizationRelationshipRepo.findAll({
            where: {
                parent_member_id: allChildId
            },
            attributes: ['rel_id'],
        }) || [];
        const subRelationshipId = subRelationship.map((relationship) => relationship.rel_id);
        
        await organizationRelationshipRepo.destroy({
            where: {
                child_member_id: allChildId,
                rel_id: {
                    [Op.notIn]: subRelationshipId
                }
            }
        });

        const newParentRelationship = await organizationRelationshipRepo.findAll({
            where: {
                child_member_id: newParentMemberId
            },
            attributes: ['parent_member_id', 'depth'],
        }) || [];

        let newRelationship: any = [];
        allChildRelationship.forEach((childRelationship) => {
            newParentRelationship.forEach((parentRelationship) => {
                newRelationship.push({
                    parent_member_id: parentRelationship.parent_member_id,
                    child_member_id: childRelationship.child_member_id,
                    depth: parentRelationship.depth + childRelationship.depth + 1,
                });
            });
        });
        await organizationRelationshipRepo.bulkCreate(newRelationship);

        await memberPositionRepo.update(
            {
                introducer_id: newParentMemberId,
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
            { memberId, newParentMemberId },
            {
                action: 'Update Organization Relationship',
                details: {
                    memberId,
                    newParentMemberId,
                    changes: {
                        removedRelationships: {
                            childCount: allChildId.length,
                            subRelationshipCount: subRelationshipId.length
                        },
                        newRelationships: {
                            count: newRelationship.length,
                            parentCount: newParentRelationship.length
                        }
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Organization relationship updated successfully'
                }
            },
            'Organization relationship updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update organization relationship',
                description: {
                    action: 'Update Organization Relationship Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        newParentMemberId: req.body.newParentMemberId,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const getUplineMembers = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const page: number = parseInt(req.query.page as string);
        const pageSize: number = parseInt(req.query.pageSize as string);

        const parentRelationships: OrganizationRelationship[] = await organizationRelationshipRepo.findAll({
            attributes: ['parent_member_id'],
            where: {
                child_member_id: memberId,
                depth: 1,
            }
        }) || [];
        const parentMemberIds = parentRelationships.map((rel) => rel.parent_member_id);

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
                },
                {
                    model: MemberPosition,
                    as: 'member_position',
                    attributes: {
                        exclude: ['created_at', 'updated_at']
                    },
                },
            ],
            where: {
                member_id: parentMemberIds
            },
            page: page,
            perPage: pageSize
        });

        return returnSuccess(
            res,
            data,
            {
                action: 'Get Upline Members',
                details: {
                    memberId,
                    page,
                    pageSize,
                    parentCount: parentMemberIds.length,
                    totalMembers: data.total,
                    hasAddress: data.data.some(member => member.member_address),
                    hasPosition: data.data.some(member => member.member_position),
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Upline members retrieved successfully'
                }
            },
            'Upline members retrieved successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to get upline members',
                description: {
                    action: 'Get Upline Members Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        page: req.query.page,
                        pageSize: req.query.pageSize,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateMemberStatus = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const { status } = req.body;

        await memberRepo.update(
            {
                status: status
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
            { memberId, status },
            {
                action: 'Update Member Status',
                details: {
                    memberId,
                    status,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member status updated successfully'
                }
            },
            'Member status updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update member status',
                description: {
                    action: 'Update Member Status Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        status: req.body.status,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
};

export const updateMemberCoolingOff = async (req: Request, res: Response, next: NextFunction) => {
    try {
        const { memberId } = req.params;
        const { cooling_off } = req.body;

        // Update member cooling off status
        await memberRepo.update(
            {
                cooling_off: cooling_off
            },
            {
                where: {
                    member_id: memberId
                },
                individualHooks: true,
            }
        );

        // Set 'canceled' in Order property
        await orderRepo.update(
            {
                status: 'canceled',
            },
            {
                where: {
                    member_id: memberId,
                },
                individualHooks: true,
            }
        );

        // Calculate stock quantity in product
        const orderIDs = await orderRepo.findAll({
            attributes: ['order_id'],
            where: {
                member_id: memberId
            },
        }) || [];

        let totalStockUpdated = 0;
        for (let orderID of orderIDs) {
            const orderItems: OrderItem[] = await orderItemRepo.findAll({
                attributes: ['product_id', 'quantity'],
                where: {
                    order_id: orderID.order_id,
                },
            }) || [];
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
        }

        // Calculate commission
        const lastTotalCommissionAmount = await MemberCommission.findOne({
            where: {
                member_id: memberId
            },
            attributes: ['total']
        });

        await MemberCommission.update(
            {
                total: 0,
            },
            {
                where: {
                    member_id: memberId
                },
                individualHooks: true
            }
        );

        let adjustmentAmount = 0;
        if (lastTotalCommissionAmount && lastTotalCommissionAmount.total > 0) {
            const [adjustment, created] = await Adjustment.findOrCreate({
                where: {
                    member_id: parseInt(memberId),
                },
                defaults: {
                    member_id: parseInt(memberId),
                    total: 0,
                    reason: 'coolingOff',
                    calculated_at: new Date(),
                },
            });

            adjustment.total = Number(adjustment.total) + lastTotalCommissionAmount.total;
            await adjustment.save();
            adjustmentAmount = lastTotalCommissionAmount.total;
        }

        return returnSuccess(
            res,
            { memberId, cooling_off },
            {
                action: 'Update Member Cooling Off',
                details: {
                    memberId,
                    cooling_off,
                    changes: {
                        orders: {
                            canceledCount: orderIDs.length,
                            totalStockUpdated
                        },
                        commission: {
                            previousTotal: lastTotalCommissionAmount?.total || 0,
                            adjustmentAmount
                        }
                    },
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'Member cooling off status updated successfully'
                }
            },
            'Member cooling off status updated successfully',
            200
        );
    } catch(e: unknown) {
        if (e instanceof Error) {
            logger.error({
                message: 'Failed to update member cooling off status',
                description: {
                    action: 'Update Member Cooling Off Error',
                    details: {
                        error: e.message,
                        memberId: req.params.memberId,
                        cooling_off: req.body.cooling_off,
                        timestamp: new Date().toISOString()
                    }
                }
            });
        }
        next(e);
    }
}; 