package com.sipanduberadat.petugas.adapters

import android.content.Context
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Filter
import com.google.android.material.textview.MaterialTextView
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.models.DesaAdat
import java.util.*

class DesaAdatArrayAdapter(
        private val ctx: Context,
        private val items: List<DesaAdat>,
        private val resourceId: Int = R.layout.layout_item_list
): ArrayAdapter<DesaAdat>(ctx, resourceId, items) {

    private var filteredItems: List<DesaAdat> = items

    override fun getView(position: Int, convertView: View?, parent: ViewGroup): View {
        val view = convertView ?: LayoutInflater.from(ctx).inflate(resourceId, parent,
                false)
        view.findViewById<MaterialTextView>(R.id.item_text_view).text = filteredItems[position].name
        return view
    }

    override fun getItem(position: Int): DesaAdat? {
        return filteredItems[position]
    }

    override fun getItemId(position: Int): Long {
        return filteredItems[position].id
    }

    override fun getCount(): Int {
        return filteredItems.size
    }

    override fun getFilter(): Filter {
        return object: Filter() {

            override fun performFiltering(p0: CharSequence?): FilterResults {
                val query = p0?.toString()?.toLowerCase(Locale.ENGLISH)
                val filterResults = FilterResults()

                filterResults.values = if (query.isNullOrEmpty()) items else items.filter {
                    it.name.toLowerCase(Locale.ENGLISH).contains(query)
                }
                return filterResults
            }

            @Suppress("UNCHECKED_CAST")
            override fun publishResults(p0: CharSequence?, p1: FilterResults?) {
                filteredItems = p1?.values as List<DesaAdat>
                notifyDataSetChanged()
            }

        }
    }

}