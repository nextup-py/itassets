<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('company_name', 'Mi Empresa S.A.');
        Setting::set('company_logo', null);

        Setting::set('pdf_title', 'Documento de Asignación de Equipamiento');

        Setting::set('pdf_intro', 'Por medio de la presente se deja constancia de la asignación temporal por parte de **:company**, a partir del **:date** del equipamiento y los accesorios descriptos en este documento a **:employee**, con documento **:document** y legajo **:legajo**, que ocupa el puesto de **:position** en :company, quien en este mismo acto adquiere la obligación del cumplimiento de las cláusulas descriptas a continuación.');

        Setting::set('pdf_clauses', [
            'A partir de la firma de este documento, el equipamiento descripto pasa a ser de exclusiva responsabilidad del firmante en lo que refiere a su utilización, mantenimiento, cuidado y confidencialidad de la información contenida en el mismo.',
            'En caso de mal funcionamiento o rotura, es de su responsabilidad comunicarse con el área de Microinformática para coordinar los pasos a seguir.',
            'La presente asignación tendrá vigencia mientras dure su relación laboral con la Cía. y mientras mantengan las condiciones que habilita o justifica dicha asignación (puesto o proyecto). Por tal motivo, si Ud. cambia de puesto o finaliza el proyecto para el que le fuera asignada deberá devolverla inmediatamente.',
            'La presente asignación es de carácter intransferible. Por tal motivo, si Ud. entrega el equipamiento asignado a su nombre a otra persona, permanecerá, ante :company, como responsable de su cuidado y utilización. En caso de que la Cía. detecte que se ha reasignado el equipamiento sin autorización previa escrita de RR.HH. estará sujeto a las sanciones que la Cía. disponga.',
            'En caso de robo extravío o pérdida deberá informar del hecho al área de Microinformática dentro de las primeras 24 hs. de sucedido y deberá enviar fotocopia de la denuncia policial realizada.',
            'En el momento en que :company disponga, podrá requerírsele que exhiba y presente el equipamiento para su inspección y verificación del estado de conservación y mantenimiento.',
            'El equipamiento asignado sólo podrá ser utilizado para cumplir con su función en la Cía. y no para otros usos personales.',
            'Está expresamente prohibido realizar la instalación de software no licenciado por :company y particularmente juegos.',
            'La compañía se reserva el derecho de reasignar el mencionado equipamiento cuando lo considere necesario.',
            'Cualquier aclaración respecto al estado en que el equipamiento o sus accesorios son entregados deberán realizarse en este documento en la línea de "observaciones".',
        ]);

        Setting::set('pdf_closing', 'En prueba de conformidad, recibo el equipamiento detallado, como así también me notifico y asumo el cumplimiento de las cláusulas de asignación.');
    }
}
