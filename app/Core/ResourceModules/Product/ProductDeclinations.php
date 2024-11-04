<?php

namespace App\Core\ResourceModules\Product;

use App\Forms\Components\SelectImage;
use App\Models\Core\Product;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Core\Value;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Core\Attribute;
use App\Core\Trait\ProductTrait;
use Illuminate\Support\HtmlString;
use App\Models\Core\AttributeValue;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Mail;
use App\Forms\Components\ModalButton;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Stevebauman\Purify\Facades\Purify;

/**
 *
 */
class ProductDeclinations
{
    use ProductTrait;

    /**
     * @var array
     */
    protected static $values = [];

    /**
     * @return mixed
     */
    public static function form(){
        $attributes = Attribute::all();
        return Section::make("declination")
            ->label(__(""))
            ->schema([
                TableRepeater::make("declinations")
                    ->relationship()
                    ->schema([
                            Select::make('value')
                                ->relationship('values', 'value')
                                ->label('Valeur')
                                ->multiple()
                                ->options(function () {
                                    return self::getValues();
                                })
                                ->preload()
                                // ->saveRelationshipsUsing(function ($component, $state, $record) {
                                //     // Synchroniser les catégories dans la table pivot sans toucher à `category_id` du modèle principal
                                //     $record->declinationValues()->attach($state);
                                // })
                                ,
                            TextInput::make("price")
                                ->minValue(0)
                                ->default(0)
                                ->required()
                                ->numeric(),
                            TextInput::make("quantity")
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->numeric()
                                ->hidden(true),
                            TextInput::make("reference")
                                ->label(__("SKU")),
                            SpatieMediaLibraryFileUpload::make('declinaison_image')
                            ->multiple()
                            ->reorderable()
                            ->imageEditor()
                            ->responsiveImages()
                            ->conversion('thumb')
                            ->optimize('webp')
                            ->columnSpan('full')
                            ->imagePreviewHeight(150)
                            ->panelLayout("grid")
                            ,
                            ])
                            ->addActionLabel(__("Add declinaition"))
                            ->defaultItems(0),
            ])->headerActions([
                Action::make('generate')
                    ->label(__("Generate"))
                    // ->modalHeading('Are you sure?')
                    ->modalDescription('Generate the combination of all declination.')
                    ->color('primary')
                    ->form([
                        // Champ select multiple pour afficher les tags des valeurs sélectionnées
                        Select::make('selected_values')
                        ->label(__("Values"))
                        ->multiple()
                        ->options(function(){
                            return  Attribute::all()->map(function($attribute) {
                                return $attribute->values->pluck('value', 'id');
                            })->toArray();
                        })->preload(false),
                        Grid::make('Attributes')
                        ->schema(
                            $attributes->map(function($attribute) {
                                    return CheckboxList::make('selected_values')
                                        ->label($attribute->name)
                                        ->options($attribute->values->pluck('value', 'id'))
                                        ->bulkToggleable()
                                        ->columns(6);

                                })->toArray()
                        ),
                    ])
                    ->action(function (Set $set, Get $get, array $data){
                       if($data["selected_values"] == null){
                            return;
                       }

                        $countCombinationExist = 0;
                        $countCombinationNotExist = 0;

                        $options = [];
                        foreach($data["selected_values"] as $p){
                            $attribute = AttributeValue::with("attribute")->find($p)->attribute;
                            self::attributesExist($options, $attribute) ? $options[$attribute->id][] = Value::all()->find($p) : $options[$attribute->id][] = Value::all()->find($p);
                        }
                        // Générer les combinaisons
                        $combinations = self::generateCombinations($options);
                        foreach( $combinations as $combination ){
                            $items = $get("declinations") ?? [];
                           $exist = self::combinationExist($combination, $items);
                            if($exist === false){
                                $items[] = [
                                    "value" => $combination,
                                    "price" => 0,
                                    "quantity" => 1,
                                    "reference" => $get("sku"),
                                    "declinaison_image" => []
                                ];

                                $countCombinationNotExist++;
                            }else{
                               $countCombinationExist++;
                            }

                            $set("declinations", $items);

                        }

                        if($countCombinationNotExist > 0){
                            Notification::make()
                                ->title($countCombinationNotExist . ' Combination generate successfully')
                                ->success()
                                ->send();
                            $set("has_declination", true);
                        }
                        if($countCombinationExist > 0){
                            Notification::make()
                            ->title($countCombinationExist. " combinations already exist, they have not been generated")
                            ->warning()
                            ->send();
                        }
                    }),
            ]);
    }

    /**
     * @param array $newCombinaison
     * @param array $tableauDeCombinaisons
     * @return bool
     */
    public static function combinationExist(array $newCombinaison, array $tableauDeCombinaisons) {
        // Trier la nouvelle combinaison pour comparaison
        $sortedNewCombinaison = $newCombinaison;
        sort($sortedNewCombinaison);

        // Parcourir chaque combinaison existante dans le tableau
        foreach ($tableauDeCombinaisons as $combinaisonExistante) {
            // Trier la combinaison existante pour comparer avec la nouvelle combinaison triée
            $sortedCombinaisonExistante = $combinaisonExistante['value'];
            sort($sortedCombinaisonExistante);

            // Comparer les deux combinaisons triées
            if ($sortedNewCombinaison === $sortedCombinaisonExistante) {
                return true; // La combinaison existe déjà
            }
        }
        return false; // La combinaison n'existe pas encore
    }

    /**
     * @param $arrays
     * @return array|array[]
     */
    public static function generateCombinations($arrays) {
        $result = [[]];
        foreach ($arrays as $property => $propertyValues) {
            $temp = [];
            foreach ($result as $resultItem) {
                foreach ($propertyValues as $propertyValue) {
                    $temp[] = array_merge($resultItem, [$propertyValue->id]);
                }
            }
            $result = $temp;
        }
        return $result;
    }

    /**
     * @return array
     */
    public static function getValues(){
        // Récupérer tous les attributs avec leurs valeurs
        $attributes = Attribute::with('values')->get();

        // Organiser les valeurs par attribut
        $options = [];
        foreach ($attributes as $attribute) {
            $options[$attribute->name] = $attribute->values->pluck('value', 'id')->toArray();
        }
        return $options;
    }
}
