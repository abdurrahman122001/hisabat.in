import winston from 'winston';
import path from 'path';

// Use Winston's built-in types
const logFormat = winston.format.combine(
  winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
  winston.format.printf((info: winston.Logform.TransformableInfo) => {
    const { timestamp, level, message, ...meta } = info;
    return `${timestamp} [${level.toUpperCase()}]: ${message} ${Object.keys(meta).length ? JSON.stringify(meta) : ''}`;
  })
);

const logger = winston.createLogger({
  level: 'info', // Levels: error, warn, info, http, verbose, debug, silly
  format: logFormat,
  transports: [
    new winston.transports.File({ filename: path.join(__dirname, '../../logs', 'app.log') }), // Logs to file
  ],
});

export default logger; 