<?php
namespace App\DataFixtures;

use App\Entity\IntercambioObjeto;
use App\Entity\Usuario;
use App\Entity\Objeto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IntercambioObjetoFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $intercambios = [
            // Intercambios completados
            [
                'precio_propuesto' => 200,
                'objeto' => 'objeto_laptop_hp_elitebook',
                'vendedor' => 'usuario_juanperez',
                'comprador' => 'usuario_mariagarcia',
                'fecha_completado' => new \DateTimeImmutable('2024-03-15'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            // Intercambios en proceso
            [
                'precio_propuesto' => 300,
                'objeto' => 'objeto_camara_canon_eos',
                'vendedor' => 'usuario_mariagarcia',
                'comprador' => 'usuario_juanperez',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            // Nuevos intercambios
            [
                'precio_propuesto' => 150,
                'objeto' => 'objeto_monitor_lg_27',
                'vendedor' => 'usuario_carloslopez',
                'comprador' => 'usuario_juanperez',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_DISPONIBLE
            ],
            [
                'precio_propuesto' => 350,
                'objeto' => 'objeto_smartphone_samsung',
                'vendedor' => 'usuario_juanperez',
                'comprador' => 'usuario_mariagarcia',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_DISPONIBLE
            ]
        ];

        foreach ($intercambios as $intercambioData) {
            $objeto = $this->getReference($intercambioData['objeto'], Objeto::class);
            
            // Solo crear intercambio si el objeto está disponible
            if ($objeto->estaDisponible()) {
                $intercambio = new IntercambioObjeto();
                $intercambio->setPrecioPropuesto($intercambioData['precio_propuesto']);
                $intercambio->setObjeto($objeto);
                $intercambio->setVendedor($this->getReference($intercambioData['vendedor'], Usuario::class));
                $intercambio->setComprador($this->getReference($intercambioData['comprador'], Usuario::class));
                $intercambio->setFechaSolicitud(new \DateTimeImmutable());
                
                if ($intercambioData['fecha_completado']) {
                    $intercambio->setFechaCompletado($intercambioData['fecha_completado']);
                    $objeto->marcarComoIntercambiado();
                } else {
                    $objeto->setEstado($intercambioData['estado_final']);
                }

                $manager->persist($intercambio);
                $this->addReference('intercambio_' . $intercambioData['objeto'], $intercambio);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UsuarioFixtures::class,
            ObjetoFixtures::class,
        ];
    }
}