<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'imagem'
    ];

    public function rules(): array
    {
        return [
            'nome' => 'required|unique:marcas,nome,'.$this->id.'|min:3',
            'imagem' => 'required|file|mimes:png'
        ];
    }

    public function feedback(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório',
            'imagem.mimes' => 'A imagem deve ser do tipo PNG',
            'nome.unique' => 'O nome da marca já existe',
            'nome.min' => 'O campo nome deve ter no mínimo 3 caracteres'
        ];
    }

    public function modelos()
    {
        //UMA marca POSUI MUITOS modelos
        return $this->hasMany('App\Models\Modelo');
    }
}
