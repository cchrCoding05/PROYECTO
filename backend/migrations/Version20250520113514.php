<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520113514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categoria (id_categoria INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, descripcion LONGTEXT DEFAULT NULL, PRIMARY KEY(id_categoria)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE etiqueta (id_etiqueta INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_6D5CA63A3A909126 (nombre), PRIMARY KEY(id_etiqueta)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE intercambio_objeto (id_intercambio INT AUTO_INCREMENT NOT NULL, id_objeto INT NOT NULL, id_vendedor INT NOT NULL, id_comprador INT NOT NULL, precio_propuesto INT NOT NULL, fecha_solicitud DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', fecha_completado DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_CF7E017017C537B (id_objeto), INDEX IDX_CF7E0170C74C74BB (id_vendedor), INDEX IDX_CF7E0170F862A056 (id_comprador), PRIMARY KEY(id_intercambio)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE intercambio_servicio (id_intercambio INT AUTO_INCREMENT NOT NULL, id_servicio INT NOT NULL, id_solicitante INT NOT NULL, cantidad_creditos INT NOT NULL, fecha_solicitud DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', fecha_completado DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', estado VARCHAR(20) NOT NULL, INDEX IDX_FDCC39AE9B5D1EBF (id_servicio), INDEX IDX_FDCC39AE6FE5CFB8 (id_solicitante), PRIMARY KEY(id_intercambio)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mensaje (id_mensaje INT AUTO_INCREMENT NOT NULL, id_emisor INT NOT NULL, id_receptor INT NOT NULL, contenido LONGTEXT NOT NULL, leido TINYINT(1) NOT NULL, fecha_envio DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_9B631D01E29930A3 (id_emisor), INDEX IDX_9B631D01B91944F2 (id_receptor), PRIMARY KEY(id_mensaje)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE negociacion_precio (id_negociacion INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_intercambio INT NOT NULL, precio_propuesto INT NOT NULL, mensaje LONGTEXT DEFAULT NULL, aceptado TINYINT(1) NOT NULL, aceptado_vendedor TINYINT(1) NOT NULL, aceptado_comprador TINYINT(1) NOT NULL, fecha_creacion DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_D61CDD99FCF8192D (id_usuario), INDEX IDX_D61CDD99188EA2DF (id_intercambio), PRIMARY KEY(id_negociacion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE negociacion_servicio (id_negociacion INT AUTO_INCREMENT NOT NULL, id_cliente INT NOT NULL, id_profesional INT NOT NULL, creditos_propuestos INT NOT NULL, estado VARCHAR(20) NOT NULL, fecha_creacion DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', fecha_aceptacion DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', fecha_completado DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_E33581E82A813255 (id_cliente), INDEX IDX_E33581E833A1E3FF (id_profesional), PRIMARY KEY(id_negociacion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE objeto (id_objeto INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion LONGTEXT NOT NULL, creditos INT NOT NULL, estado INT NOT NULL, fecha_creacion DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', imagen VARCHAR(255) DEFAULT NULL, INDEX IDX_274BE696FCF8192D (id_usuario), PRIMARY KEY(id_objeto)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE servicio (id_servicio INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_categoria INT NOT NULL, titulo VARCHAR(100) NOT NULL, descripcion LONGTEXT NOT NULL, creditos INT NOT NULL, activo TINYINT(1) NOT NULL, imagen VARCHAR(255) DEFAULT NULL, INDEX IDX_CB86F22AFCF8192D (id_usuario), INDEX IDX_CB86F22ACE25AE0A (id_categoria), PRIMARY KEY(id_servicio)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE servicio_etiquetas (id_servicio INT NOT NULL, id_etiqueta INT NOT NULL, INDEX IDX_2BC2D42B9B5D1EBF (id_servicio), INDEX IDX_2BC2D42B3D874AAF (id_etiqueta), PRIMARY KEY(id_servicio, id_etiqueta)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transaccion_credito (id_transaccion INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_intercambio_servicio INT DEFAULT NULL, id_intercambio_objeto INT DEFAULT NULL, cantidad INT NOT NULL, tipo VARCHAR(20) NOT NULL, descripcion LONGTEXT DEFAULT NULL, fecha_creacion DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5B3CB5A0FCF8192D (id_usuario), INDEX IDX_5B3CB5A0F13CC80A (id_intercambio_servicio), INDEX IDX_5B3CB5A04C5CB3DF (id_intercambio_objeto), PRIMARY KEY(id_transaccion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE usuario (id_usuario INT AUTO_INCREMENT NOT NULL, nombre_usuario VARCHAR(50) NOT NULL, correo VARCHAR(100) NOT NULL, contrasena VARCHAR(255) NOT NULL, foto_perfil VARCHAR(1000) DEFAULT NULL, descripcion LONGTEXT DEFAULT NULL, profesion VARCHAR(100) DEFAULT NULL, fecha_registro DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', creditos INT NOT NULL, valoracion_promedio DOUBLE PRECISION DEFAULT NULL, ventas_realizadas INT NOT NULL, token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2265B05DD67CF11D (nombre_usuario), UNIQUE INDEX UNIQ_2265B05D77040BC9 (correo), PRIMARY KEY(id_usuario)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE valoracion (id_valoracion INT AUTO_INCREMENT NOT NULL, id_usuario INT NOT NULL, id_intercambio_servicio INT DEFAULT NULL, id_intercambio_objeto INT DEFAULT NULL, puntuacion INT NOT NULL, comentario LONGTEXT DEFAULT NULL, fecha_creacion DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_6D3DE0F4FCF8192D (id_usuario), INDEX IDX_6D3DE0F4F13CC80A (id_intercambio_servicio), INDEX IDX_6D3DE0F44C5CB3DF (id_intercambio_objeto), PRIMARY KEY(id_valoracion)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto ADD CONSTRAINT FK_CF7E017017C537B FOREIGN KEY (id_objeto) REFERENCES objeto (id_objeto)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto ADD CONSTRAINT FK_CF7E0170C74C74BB FOREIGN KEY (id_vendedor) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto ADD CONSTRAINT FK_CF7E0170F862A056 FOREIGN KEY (id_comprador) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_servicio ADD CONSTRAINT FK_FDCC39AE9B5D1EBF FOREIGN KEY (id_servicio) REFERENCES servicio (id_servicio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_servicio ADD CONSTRAINT FK_FDCC39AE6FE5CFB8 FOREIGN KEY (id_solicitante) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01E29930A3 FOREIGN KEY (id_emisor) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mensaje ADD CONSTRAINT FK_9B631D01B91944F2 FOREIGN KEY (id_receptor) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_precio ADD CONSTRAINT FK_D61CDD99FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_precio ADD CONSTRAINT FK_D61CDD99188EA2DF FOREIGN KEY (id_intercambio) REFERENCES intercambio_objeto (id_intercambio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_servicio ADD CONSTRAINT FK_E33581E82A813255 FOREIGN KEY (id_cliente) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_servicio ADD CONSTRAINT FK_E33581E833A1E3FF FOREIGN KEY (id_profesional) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objeto ADD CONSTRAINT FK_274BE696FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22AFCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio ADD CONSTRAINT FK_CB86F22ACE25AE0A FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio_etiquetas ADD CONSTRAINT FK_2BC2D42B9B5D1EBF FOREIGN KEY (id_servicio) REFERENCES servicio (id_servicio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio_etiquetas ADD CONSTRAINT FK_2BC2D42B3D874AAF FOREIGN KEY (id_etiqueta) REFERENCES etiqueta (id_etiqueta)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito ADD CONSTRAINT FK_5B3CB5A0FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito ADD CONSTRAINT FK_5B3CB5A0F13CC80A FOREIGN KEY (id_intercambio_servicio) REFERENCES intercambio_servicio (id_intercambio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito ADD CONSTRAINT FK_5B3CB5A04C5CB3DF FOREIGN KEY (id_intercambio_objeto) REFERENCES intercambio_objeto (id_intercambio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F4FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuario (id_usuario)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F4F13CC80A FOREIGN KEY (id_intercambio_servicio) REFERENCES intercambio_servicio (id_intercambio)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion ADD CONSTRAINT FK_6D3DE0F44C5CB3DF FOREIGN KEY (id_intercambio_objeto) REFERENCES intercambio_objeto (id_intercambio)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto DROP FOREIGN KEY FK_CF7E017017C537B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto DROP FOREIGN KEY FK_CF7E0170C74C74BB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_objeto DROP FOREIGN KEY FK_CF7E0170F862A056
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_servicio DROP FOREIGN KEY FK_FDCC39AE9B5D1EBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intercambio_servicio DROP FOREIGN KEY FK_FDCC39AE6FE5CFB8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01E29930A3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE mensaje DROP FOREIGN KEY FK_9B631D01B91944F2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_precio DROP FOREIGN KEY FK_D61CDD99FCF8192D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_precio DROP FOREIGN KEY FK_D61CDD99188EA2DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_servicio DROP FOREIGN KEY FK_E33581E82A813255
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE negociacion_servicio DROP FOREIGN KEY FK_E33581E833A1E3FF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE objeto DROP FOREIGN KEY FK_274BE696FCF8192D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22AFCF8192D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio DROP FOREIGN KEY FK_CB86F22ACE25AE0A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio_etiquetas DROP FOREIGN KEY FK_2BC2D42B9B5D1EBF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE servicio_etiquetas DROP FOREIGN KEY FK_2BC2D42B3D874AAF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito DROP FOREIGN KEY FK_5B3CB5A0FCF8192D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito DROP FOREIGN KEY FK_5B3CB5A0F13CC80A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaccion_credito DROP FOREIGN KEY FK_5B3CB5A04C5CB3DF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F4FCF8192D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F4F13CC80A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE valoracion DROP FOREIGN KEY FK_6D3DE0F44C5CB3DF
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categoria
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE etiqueta
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE intercambio_objeto
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE intercambio_servicio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mensaje
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE negociacion_precio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE negociacion_servicio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE objeto
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE servicio
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE servicio_etiquetas
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transaccion_credito
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE usuario
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE valoracion
        SQL);
    }
}
