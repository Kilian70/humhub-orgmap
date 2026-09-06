<?php

namespace humhub\modules\orgmap\tests\codeception\unit;

use humhub\modules\orgmap\models\Asset;
use orgmap\OrgMapTestCase;
use yii\web\UploadedFile;

class AssetUploadValidationTest extends OrgMapTestCase
{
	private array $temporaryFiles = [];

	protected function tearDown(): void
	{
		foreach ($this->temporaryFiles as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}

		parent::tearDown();
	}

	public function testValidPngIsAccepted(): void
	{
		$model = $this->assetWithUpload('valid.png', $this->validPng());

		$this->assertTrue($model->validate(), json_encode($model->getErrors()));
	}

	public function testExecutableContentWithImageExtensionIsRejected(): void
	{
		$model = $this->assetWithUpload('attack.png', '<?php echo "unsafe";');

		$this->assertFalse($model->validate());
		$this->assertArrayHasKey('imageFile', $model->getErrors());
	}

	public function testTruncatedImageIsRejected(): void
	{
		$model = $this->assetWithUpload('broken.png', "\x89PNG\r\n\x1a\n");

		$this->assertFalse($model->validate());
		$this->assertArrayHasKey('imageFile', $model->getErrors());
	}

	public function testFileLargerThanTenMegabytesIsRejected(): void
	{
		$model = $this->assetWithUpload(
			'too-large.png',
			$this->validPng() . str_repeat("\0", 10 * 1024 * 1024)
		);

		$this->assertFalse($model->validate());
		$this->assertArrayHasKey('imageFile', $model->getErrors());
	}

	public function testManipulatedExtremeDimensionsAreRejected(): void
	{
		$model = $this->assetWithUpload('pixel-bomb.png', $this->pngWithDimensions(10000, 10000));

		$this->assertFalse($model->validate());
		$this->assertArrayHasKey('imageFile', $model->getErrors());
	}

	private function assetWithUpload(string $name, string $contents): Asset
	{
		$tempName = tempnam(sys_get_temp_dir(), 'orgmap-upload-');
		file_put_contents($tempName, $contents);
		$this->temporaryFiles[] = $tempName;

		$model = new Asset(['title' => 'Test image', 'type' => 'image']);
		$model->imageFile = new UploadedFile([
			'name' => $name,
			'tempName' => $tempName,
			'type' => 'image/png',
			'size' => filesize($tempName),
			'error' => UPLOAD_ERR_OK,
		]);

		return $model;
	}

	private function validPng(): string
	{
		return base64_decode(
			'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
			true
		);
	}

	private function pngWithDimensions(int $width, int $height): string
	{
		$png = $this->validPng();
		$data = pack('NNCCCCC', $width, $height, 8, 4, 0, 0, 0);
		$chunkType = 'IHDR';
		$crc = hex2bin(hash('crc32b', $chunkType . $data));

		return substr($png, 0, 8) . pack('N', strlen($data)) . $chunkType . $data . $crc . substr($png, 33);
	}
}
