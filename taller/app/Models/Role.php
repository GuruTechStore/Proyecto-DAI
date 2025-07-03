use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'descripcion',
        'nivel_permiso',
    ];

    protected $casts = [
        'nivel_permiso' => 'integer',
    ];
}
