const register = async(req: Request, res: Response, next: NextFunction) => {
    const { email, password } = req.body;
    try {
        logger.info({
            message: 'Starting user registration process',
            description: {
                action: 'Registration Initiation',
                details: {
                    email: email,
                    timestamp: new Date().toISOString()
                }
            }
        });

        const existUser = await userRepo.findByKey(email, 'email');
        if(existUser) {
            logger.warn({
                message: 'Registration failed - Email already exists',
                description: {
                    action: 'Registration Validation',
                    details: {
                        email: email,
                        timestamp: new Date().toISOString(),
                        reason: 'Email already exists in system'
                    }
                }
            });
            throw new BadRequest('User with same email already exists.');
        }
        else {
            let userData: any = {
                email: email,
                password: await bcrypt.hash(password, 10),
            };
            const user = await userRepo.create(userData);
            
            logger.info({
                message: 'User registration successful',
                description: {
                    action: 'User Registration',
                    details: {
                        email: user.email,
                        userId: user.id,
                        timestamp: new Date().toISOString(),
                        status: 'success'
                    }
                }
            });

            return returnSuccess(
                res,
                {
                    email: user.email,
                    created_at: user.created_at,
                },
                {
                    action: 'User Registration',
                    details: {
                        email: user.email,
                        registrationDate: user.created_at,
                        status: 'success',
                        message: 'New user account created successfully'
                    }
                },
                'User registered successfully',
                201
            );
        }
    } catch(e) {
        logger.error({
            message: 'Registration process failed',
            description: {
                action: 'Registration Error',
                details: {
                    email: email,
                    error: e.message,
                    timestamp: new Date().toISOString()
                }
            }
        });
        next(e);
    }
};

const login = async (req: Request, res: Response, next: NextFunction) => {
    const { email, password } = req.body;
    try {
        logger.info({
            message: 'Starting user login process',
            description: {
                action: 'Login Initiation',
                details: {
                    email: email,
                    timestamp: new Date().toISOString()
                }
            }
        });

        const existUser = await userRepo.findByKey(email, 'email');
        if (!existUser) {
            logger.warn({
                message: 'Login failed - User does not exist',
                description: {
                    action: 'Login Validation',
                    details: {
                        email: email,
                        timestamp: new Date().toISOString(),
                        reason: 'User not found in system'
                    }
                }
            });
            throw new BadRequest("User doesn't exist.");
        }

        const passwordMatch = await bcrypt.compareSync(password, existUser.password);
        if (!passwordMatch) {
            logger.warn({
                message: 'Login failed - Invalid password',
                description: {
                    action: 'Login Validation',
                    details: {
                        email: email,
                        timestamp: new Date().toISOString(),
                        reason: 'Incorrect password provided'
                    }
                }
            });
            throw new BadRequest('Password is not correct.');
        }

        if (!existUser.status) {
            logger.warn({
                message: 'Login failed - User is deactive',
                description: {
                    action: 'Login Validation',
                    details: {
                        email: email,
                        timestamp: new Date().toISOString(),
                        reason: 'User account is deactivated'
                    }
                }
            });
            throw new NotAuthorized('User is deactive.');
        }

        existUser.last_login_at = new Date();
        await existUser.save();

        const payload = {
            user_id: existUser.user_id,
            email: existUser.email,
            role_id: existUser.role_id,
        };

        const accessToken = jwt.sign(
            payload,
            process.env.JWTSECRET as string,
            { expiresIn: parseFloat(process.env.JWTEXPIREDTIME as string) * 3600 },
        );

        logger.info({
            message: 'User login successful',
            description: {
                action: 'User Login',
                details: {
                    email: existUser.email,
                    userId: existUser.user_id,
                    timestamp: new Date().toISOString(),
                    status: 'success'
                }
            }
        });

        return returnSuccess(
            res,
            {
                ...payload,
                token: accessToken,
            },
            {
                action: 'User Login',
                details: {
                    email: existUser.email,
                    userId: existUser.user_id,
                    loginTime: existUser.last_login_at,
                    status: 'success',
                    message: 'User logged in successfully'
                }
            },
            'Login successful',
            200
        );
    } catch (e) {
        logger.error({
            message: 'Login process failed',
            description: {
                action: 'Login Error',
                details: {
                    email: email,
                    error: e.message,
                    timestamp: new Date().toISOString()
                }
            }
        });
        next(e);
    }
};

const getMe = async (req: Request, res: Response, next: NextFunction) => {
    const token: string | undefined | null = req.header('Authorization')?.replace('Bearer', '').trim();
    try {
        logger.info({
            message: 'Starting get user profile process',
            description: {
                action: 'Get User Profile',
                details: {
                    timestamp: new Date().toISOString(),
                    hasToken: !!token
                }
            }
        });

        const userData = await getAuthUser(token);
        
        logger.info({
            message: 'User profile retrieved successfully',
            description: {
                action: 'Get User Profile',
                details: {
                    userId: userData.user_id,
                    email: userData.email,
                    timestamp: new Date().toISOString(),
                    status: 'success'
                }
            }
        });

        return returnSuccess(
            res,
            userData,
            {
                action: 'Get User Profile',
                details: {
                    userId: userData.user_id,
                    email: userData.email,
                    timestamp: new Date().toISOString(),
                    status: 'success',
                    message: 'User profile retrieved successfully'
                }
            },
            'Profile retrieved successfully',
            200
        );
    } catch (e) {
        logger.error({
            message: 'Failed to get user profile',
            description: {
                action: 'Get User Profile Error',
                details: {
                    error: e.message,
                    timestamp: new Date().toISOString(),
                    hasToken: !!token
                }
            }
        });
        next(e);
    }
}; 