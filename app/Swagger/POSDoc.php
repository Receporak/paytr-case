<?php

namespace App\Swagger;

/**
 * @OA\Tag(
 *     name="POS",
 *     description="POS işlemleri"
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pos/select",
 *     summary="En düşük komisyonlu POS'u seç",
 *     description="Verilen para birimi, kart tipi ve taksit sayısına göre en uygun (en düşük komisyonlu) POS'u döner.",
 *     operationId="posSelect",
 *     tags={"POS"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"currency","card_type","installment"},
 *             @OA\Property(property="currency", type="string", enum={"TRY","USD","EUR"}, example="TRY", description="Para birimi"),
 *             @OA\Property(property="card_type", type="string", enum={"credit","debit","unknown"}, example="credit", description="Kart tipi"),
 *             @OA\Property(property="installment", type="integer", minimum=1, example=3, description="Taksit sayısı")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="En düşük komisyonlu POS başarıyla bulundu",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="code", type="integer", example=200),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="filters",
 *                     type="object",
 *                     @OA\Property(property="installment", type="integer", example=3),
 *                     @OA\Property(property="currency", type="string", example="TRY"),
 *                     @OA\Property(property="card_type", type="string", example="credit"),
 *                     @OA\Property(property="card_brand", type="string", example="visa")
 *                 ),
 *                 @OA\Property(
 *                     property="overall_min",
 *                     type="object",
 *                     @OA\Property(property="pos_name", type="string", example="Garanti"),
 *                     @OA\Property(property="card_type", type="string", example="credit"),
 *                     @OA\Property(property="card_brand", type="string", example="visa"),
 *                     @OA\Property(property="installment", type="integer", example=3),
 *                     @OA\Property(property="currency", type="string", example="TRY"),
 *                     @OA\Property(property="commission_rate", type="number", format="float", example=1.75)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validasyon hatası",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="The currency field is required."),
 *             @OA\Property(property="data", nullable=true, example=null),
 *             @OA\Property(property="code", type="integer", example=400)
 *         )
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/v1/pos/rates-sync",
 *     summary="POS komisyon oranlarını senkronize et",
 *     description="Harici API'den POS komisyon oranlarını çekme işlemini kuyruğa alır.",
 *     operationId="posRatesSync",
 *     tags={"POS"},
 *     @OA\Response(
 *         response=200,
 *         description="Senkronizasyon kuyruğa alındı",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Success"),
 *             @OA\Property(property="data", type="string", example="Senkronizasyon kuyruğa alındı"),
 *             @OA\Property(property="code", type="integer", example=200)
 *         )
 *     )
 * )
 */
class POSDoc
{
}
