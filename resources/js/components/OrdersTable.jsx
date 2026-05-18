import React from 'react'
import { Link } from '@inertiajs/react'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const estados = {
    'pendiente': { label: 'Pendiente', variant: 'secondary' },
    'entregado': { label: 'Entregado', variant: 'default' },
    'cancelado': { label: 'Cancelado', variant: 'destructive' },
}

export default function OrdersTable({ pedidos }) {
    if (!pedidos || pedidos.data.length === 0) {
        return (
            <p className="text-gray-500 dark:text-gray-400 text-center py-8">
                No hay pedidos en esta sección.
            </p>
        )
    }

    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-[100px]">ID</TableHead>
                        <TableHead>Cliente</TableHead>
                        <TableHead>Fecha Entrega</TableHead>
                        <TableHead>Productos</TableHead>
                        <TableHead className="text-right">Total</TableHead>
                        <TableHead className="text-center">Estado</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {pedidos.data.map((pedido) => (
                        <TableRow key={pedido.id}>
                            <TableCell className="font-medium">#{pedido.id}</TableCell>
                            <TableCell>{pedido.cliente?.nombre ?? '—'}</TableCell>
                            <TableCell>{pedido.fecha_entrega}</TableCell>
                            <TableCell>
                                <div className="flex flex-wrap gap-1">
                                    {pedido.productos.map((producto) => (
                                        <Badge key={producto.id} variant="outline">
                                            {producto.nombre}
                                            <span className="ml-1 text-gray-500 dark:text-gray-400">
                                                x{producto.pivot?.cantidad ?? 1}
                                            </span>
                                        </Badge>
                                    ))}
                                </div>
                            </TableCell>
                            <TableCell className="text-right">${parseFloat(pedido.total).toFixed(2)}</TableCell>
                            <TableCell className="text-center">
                                <Badge variant={estados[pedido.estado]?.variant ?? 'outline'}>
                                    {estados[pedido.estado]?.label ?? pedido.estado}
                                </Badge>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    )
}