import React from 'react'
import AuthenticatedLayout from '../Layouts/Authenticated'
import OrdersTable from '@/components/OrdersTable'
import Pagination from '@/components/Pagination'
import TabNavigation from '@/components/TabNavigation'

const sections = [
    { name: 'index', label: 'Por Enviar' },
    { name: 'retrasados', label: 'Retrasados' },
    { name: 'entregados', label: 'Entregados' },
    { name: 'cancelados', label: 'Cancelados' },
]

const titulos = {
    'index': 'Pedidos Por Enviar',
    'retrasados': 'Pedidos Retrasados',
    'entregados': 'Pedidos Entregados',
    'cancelados': 'Pedidos Cancelados',
}

export default function Dashboard({ seccion, pedidos }) {
    const titulo = titulos[seccion] || 'Dashboard'

    return (
        <AuthenticatedLayout>
            <div className="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div className="flex items-center justify-between mb-4">
                    <h2 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">{titulo}</h2>
                    <span className="text-sm text-gray-500 dark:text-gray-400">
                        Total: {pedidos.total} pedidos
                    </span>
                </div>

                <TabNavigation currentSection={seccion} sections={sections} />

                <OrdersTable pedidos={pedidos} />

                <Pagination links={pedidos.links} />
            </div>
        </AuthenticatedLayout>
    )
}
