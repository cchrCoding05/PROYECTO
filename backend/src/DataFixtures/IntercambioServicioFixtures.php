<?php
namespace App\DataFixtures;

use App\Entity\IntercambioServicio;
use App\Entity\Usuario;
use App\Entity\Servicio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntercambioServicioFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $intercambiosServicios = [
            [
                'servicio' => 0,
                'solicitante' => 'mariagonzalez',
                'cantidad_creditos' => 80,
                'fecha_solicitud' => new \DateTimeImmutable('-15 days'),
                'fecha_completado' => new \DateTimeImmutable('-10 days')
            ],
            // Your other fixtures
        ];

        foreach ($intercambiosServicios as $index => $intercambioData) {
            $intercambio = new IntercambioServicio();
            
            try {
                $servicioReference = 'servicio-' . $intercambioData['servicio'];
                $usuarioReference = 'usuario-' . $intercambioData['solicitante'];
                
                // Make sure to specify the class type for hasReference
                if ($this->hasReference($servicioReference, Servicio::class) && 
                    $this->hasReference($usuarioReference, Usuario::class)) {
                    
                    $intercambio->setServicio($this->getReference($servicioReference, Servicio::class));
                    $intercambio->setSolicitante($this->getReference($usuarioReference, Usuario::class));
                    $intercambio->setCantidadCreditos($intercambioData['cantidad_creditos']);
                    $intercambio->setFechaSolicitud($intercambioData['fecha_solicitud']);
                    
                    if ($intercambioData['fecha_completado']) {
                        $intercambio->setFechaCompletado($intercambioData['fecha_completado']);
                    }
                    
                    $manager->persist($intercambio);
                    
                    // Referencias para usar en otras fixtures
                    $this->addReference('intercambio-servicio-' . $index, $intercambio);
                } else {
                    echo "Reference not found: $servicioReference or $usuarioReference\n";
                }
            } catch (\Exception $e) {
                // Log the error but continue with other fixtures
                echo "Error loading fixture: " . $e->getMessage() . "\n";
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ServicioFixtures::class,
            UsuarioFixtures::class,
        ];
    }
}