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
            // Intercambios existentes
            [
                'precio_propuesto' => 200,
                'objeto' => 'objeto_laptop_hp_elitebook',
                'vendedor' => 'usuario_juanperez',
                'comprador' => 'usuario_mariagarcia',
                'fecha_completado' => new \DateTimeImmutable('2024-03-15'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 300,
                'objeto' => 'objeto_camara_canon_eos_r5',
                'vendedor' => 'usuario_mariagarcia',
                'comprador' => 'usuario_juanperez',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 150,
                'objeto' => 'objeto_monitor_lg_ultragear_27',
                'vendedor' => 'usuario_carloslopez',
                'comprador' => 'usuario_juanperez',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_DISPONIBLE
            ],
            [
                'precio_propuesto' => 350,
                'objeto' => 'objeto_smartphone_samsung_s21',
                'vendedor' => 'usuario_juanperez',
                'comprador' => 'usuario_mariagarcia',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_DISPONIBLE
            ],
            // Nuevos intercambios
            [
                'precio_propuesto' => 800,
                'objeto' => 'objeto_macbook_pro_m1',
                'vendedor' => 'usuario_sofia_tech',
                'comprador' => 'usuario_pablo_design',
                'fecha_completado' => new \DateTimeImmutable('2024-03-25'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 450,
                'objeto' => 'objeto_wacom_cintiq_22',
                'vendedor' => 'usuario_pablo_design',
                'comprador' => 'usuario_sofia_tech',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 550,
                'objeto' => 'objeto_dji_mavic_air_2',
                'vendedor' => 'usuario_laura_marketing',
                'comprador' => 'usuario_diego_dev',
                'fecha_completado' => new \DateTimeImmutable('2024-03-28'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 480,
                'objeto' => 'objeto_playstation_5',
                'vendedor' => 'usuario_diego_dev',
                'comprador' => 'usuario_laura_marketing',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 380,
                'objeto' => 'objeto_ipad_pro_11',
                'vendedor' => 'usuario_carmen_art',
                'comprador' => 'usuario_roberto_consultant',
                'fecha_completado' => new \DateTimeImmutable('2024-03-30'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 1200,
                'objeto' => 'objeto_microsoft_surface_studio',
                'vendedor' => 'usuario_roberto_consultant',
                'comprador' => 'usuario_carmen_art',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 150,
                'objeto' => 'objeto_raspberry_pi_4_kit',
                'vendedor' => 'usuario_elena_teacher',
                'comprador' => 'usuario_miguel_entrepreneur',
                'fecha_completado' => new \DateTimeImmutable('2024-04-01'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 320,
                'objeto' => 'objeto_oculus_quest_2',
                'vendedor' => 'usuario_miguel_entrepreneur',
                'comprador' => 'usuario_patricia_ux',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 280,
                'objeto' => 'objeto_gopro_hero_9',
                'vendedor' => 'usuario_patricia_ux',
                'comprador' => 'usuario_elena_teacher',
                'fecha_completado' => new \DateTimeImmutable('2024-04-05'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ],
            [
                'precio_propuesto' => 350,
                'objeto' => 'objeto_nintendo_switch_oled',
                'vendedor' => 'usuario_sofia_tech',
                'comprador' => 'usuario_pablo_design',
                'fecha_completado' => null,
                'estado_final' => Objeto::ESTADO_RESERVADO
            ],
            [
                'precio_propuesto' => 120,
                'objeto' => 'objeto_kindle_paperwhite',
                'vendedor' => 'usuario_pablo_design',
                'comprador' => 'usuario_sofia_tech',
                'fecha_completado' => new \DateTimeImmutable('2024-04-10'),
                'estado_final' => Objeto::ESTADO_INTERCAMBIADO
            ]
        ];

        foreach ($intercambios as $intercambioData) {
            try {
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
            } catch (\Exception $e) {
                // Si el objeto no existe, continuamos con el siguiente intercambio
                continue;
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