<?php
use Drupal\node\Entity\Node;

$recipes = [
  [
    'title' => 'Fresh Beef & Broccoli',
    'ingredients' => '80% Lean Beef, 10% Bone, 10% Liver, Steamed Broccoli',
    'instructions' => 'Mix ingredients cold. Serve immediately.',
  ],
  [
    'title' => 'High-Protein Turkey Mix',
    'ingredients' => 'Ground Turkey, Spinach, Sweet Potato, Salmon Oil',
    'instructions' => 'Lightly steam the spinach and turkey before mixing.',
  ],
];

foreach ($recipes as $recipeData) {
  $node = Node::create([
    'type' => 'recipe',
    'title' => $recipeData['title'],
    'field_ingredients' => $recipeData['ingredients'],
    'field_instructions' => $recipeData['instructions'],
    'status' => 1, // Published
  ]);
  $node->save();
  echo "Created recipe: " . $node->getTitle() . "\n";
}