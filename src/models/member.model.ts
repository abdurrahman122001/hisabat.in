import { Model, DataTypes } from 'sequelize';
import { sequelize } from '../config/database';
import { MemberAddress } from './member_address.model';
import { MemberBankAccount } from './member_bank_account.model';
import { MemberPosition } from './member_position.model';

export class Member extends Model {
    public member_id!: number;
    public name!: string;
    public email!: string | null;
    public phone!: string | null;
    public status!: string;
    public cooling_off!: boolean;
    public auto_purchase!: boolean;
    public contract_date!: Date;

    // Define associations
    public readonly member_address?: MemberAddress;
    public readonly member_bank_account?: MemberBankAccount;
    public readonly member_position?: MemberPosition;
}

Member.init(
    {
        member_id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        name: {
            type: DataTypes.STRING,
            allowNull: false,
        },
        email: {
            type: DataTypes.STRING,
            allowNull: true,
        },
        phone: {
            type: DataTypes.STRING,
            allowNull: true,
        },
        status: {
            type: DataTypes.STRING,
            allowNull: false,
        },
        cooling_off: {
            type: DataTypes.BOOLEAN,
            allowNull: false,
            defaultValue: false,
        },
        auto_purchase: {
            type: DataTypes.BOOLEAN,
            allowNull: false,
            defaultValue: false,
        },
        contract_date: {
            type: DataTypes.DATE,
            allowNull: false,
        },
    },
    {
        sequelize,
        tableName: 'members',
        timestamps: true,
    }
);

// Define associations
Member.hasOne(MemberAddress, {
    foreignKey: 'member_id',
    as: 'member_address',
});

Member.hasOne(MemberBankAccount, {
    foreignKey: 'member_id',
    as: 'member_bank_account',
});

Member.hasOne(MemberPosition, {
    foreignKey: 'member_id',
    as: 'member_position',
}); 