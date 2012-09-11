class VehicleFile 
{   $modx;
    $builder;

    __construct(&$modx)
    {//Only initialize if we need to...
    if (self::$builder == null)
            self::$builder = new modPackageBuilder($modx);
    }

    protected function export()
    {
    }

    protected function build($data, $attr)
    {//Send to XPDO:
        $new = $builder->createVehicle($data, $attr);
        $builder->putVehicle($new);
    }
}